#!/usr/bin/env python3
"""Regenerasi resources/fonts/material-symbols-subset.woff2.

Memangkas font ikon Material Symbols Outlined (Google, ~448 KiB, ~3000 ikon)
menjadi hanya ikon yang dipakai situs + cadangan travel (~14 KiB).

Butuh: pip install fonttools brotli
Jalankan dari root project: python resources/fonts/material-symbols-subset.py

Jika menambah ikon baru di Blade, tambahkan namanya ke USED lalu jalankan ulang.
"""
import os, urllib.request
from fontTools.ttLib import TTFont
from fontTools import subset

# woff2 axis FILL@0..1 dari Google Fonts (UA Chrome). Update jika versi font berubah.
SRC_URL = "https://fonts.gstatic.com/s/materialsymbolsoutlined/v344/kJF4BvYX7BgnkSrUwT8OhrdQw4oELdPIeeII9v6oDMzBwG-RpA6RzaxHMPdY40KH8nGzv3fzfVJU22ZZLsYEpzC_1ver5Y0.woff2"
HERE = os.path.dirname(os.path.abspath(__file__))
FULL = os.path.join(HERE, "_ms-full.woff2")
OUT  = os.path.join(HERE, "material-symbols-subset.woff2")

USED = "account_balance add arrow_forward arrow_upward article badge calendar_month calendar_today call cancel chat chat_bubble check check_circle chevron_left chevron_right close content_copy diamond done download expand_more favorite flight_takeoff fullscreen grade group image_not_supported info link location_on mail map menu_book open_in_new payments photo_library public receipt_long remove schedule search share star support_agent task_alt travel_explore verified_user videocam visibility help zoom_in zoom_out"
EXTRA = "hotel restaurant restaurant_menu directions_car directions_bus directions_boat flight flight_land train local_cafe wifi pool beach_access hiking photo_camera local_activity king_bed person_pin water terrain forest park landscape ac_unit local_bar local_dining spa fitness_center luggage backpack explore tour place near_me navigation sailing kayaking nights_stay wb_sunny umbrella set_meal lunch_dining airport_shuttle car_rental currency_exchange language translate favorite verified workspace_premium military_tech home phone email pin_drop"
names = set((USED + " " + EXTRA).split())

if not os.path.exists(FULL):
    req = urllib.request.Request(SRC_URL, headers={"User-Agent": "Mozilla/5.0 Chrome/120.0"})
    open(FULL, "wb").write(urllib.request.urlopen(req).read())

f = TTFont(FULL)
rev = {g: chr(cp) for cp, g in f.getBestCmap().items()}
def lig_string(first, comp):
    try: return "".join(rev[g] for g in ([first] + list(comp)))
    except KeyError: return None

kept = dropped = 0
for lk in f["GSUB"].table.LookupList.Lookup:
    for st in lk.SubTable:
        inner = st.ExtSubTable if st.__class__.__name__ == "ExtensionSubst" else st
        if inner.__class__.__name__ != "LigatureSubst":
            continue
        for first, ligs in list(inner.ligatures.items()):
            newligs = [lig for lig in ligs if lig_string(first, lig.Component) in names]
            kept += len(newligs); dropped += len(ligs) - len(newligs)
            if newligs: inner.ligatures[first] = newligs
            else: del inner.ligatures[first]

pruned = os.path.join(HERE, "_ms-pruned.ttf")
f.save(pruned)
subset.main([pruned, f"--output-file={OUT}", "--flavor=woff2",
             "--text=" + " ".join(sorted(names)), "--glyph-names"])
os.remove(pruned)
print(f"OK: {kept} ikon -> {OUT} ({os.path.getsize(OUT)/1024:.1f} KiB)")
