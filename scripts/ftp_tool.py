"""Alat FTP kecil untuk deploy berkas satuan ke Rumah Web.

Kata sandi dibaca dari variabel lingkungan FTP_PASS (jangan ditulis di berkas).

Pemakaian:
  python scripts/ftp_tool.py put <lokal> <jauh> [<lokal> <jauh> ...]
  python scripts/ftp_tool.py rm <jauh> [<jauh> ...]
  python scripts/ftp_tool.py ls <folder-jauh>
  python scripts/ftp_tool.py get <jauh> <lokal>
"""
import ftplib
import os
import sys

HOST = os.environ.get('FTP_HOST', 'ftp.sujaitobasumatera.com')
USER = os.environ.get('FTP_USER', 'admin@sujaitobasumatera.com')
PASS = os.environ['FTP_PASS']


def connect():
    ftp = ftplib.FTP(HOST, timeout=60)
    ftp.login(USER, PASS)
    return ftp


def ensure_dir(ftp, remote_path):
    parts = remote_path.replace('\\', '/').split('/')[:-1]
    cur = ''
    for p in parts:
        if not p:
            continue
        cur = f'{cur}/{p}' if cur else p
        try:
            ftp.mkd(cur)
        except ftplib.error_perm:
            pass


def main():
    if len(sys.argv) < 2:
        print(__doc__)
        return 1
    cmd, args = sys.argv[1], sys.argv[2:]
    ftp = connect()
    try:
        if cmd == 'put':
            for i in range(0, len(args), 2):
                local, remote = args[i], args[i + 1]
                ensure_dir(ftp, remote)
                with open(local, 'rb') as f:
                    ftp.storbinary(f'STOR {remote}', f)
                print(f'PUT {local} -> {remote}')
        elif cmd == 'rm':
            for remote in args:
                try:
                    ftp.delete(remote)
                    print(f'RM  {remote}')
                except ftplib.error_perm as e:
                    print(f'RM  {remote} gagal: {e}')
        elif cmd == 'ls':
            ftp.retrlines(f'LIST {args[0] if args else "."}')
        elif cmd == 'get':
            with open(args[1], 'wb') as f:
                ftp.retrbinary(f'RETR {args[0]}', f.write)
            print(f'GET {args[0]} -> {args[1]}')
        else:
            print(__doc__)
            return 1
    finally:
        ftp.quit()
    return 0


if __name__ == '__main__':
    sys.exit(main())
