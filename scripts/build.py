#!/usr/bin/env python3
"""Build a reproducible WordPress-installable ZIP using an explicit allowlist."""
import hashlib
import re
import zipfile
from pathlib import Path

root = Path(__file__).resolve().parents[1]
slug = "netmilk-wpml-component-compat"
plugin = root / "plugin" / slug
main = plugin / (slug + ".php")
version = re.search(r"^ \* Version: ([0-9]+\.[0-9]+\.[0-9]+)$", main.read_text(), re.M).group(1)
assert "Stable tag: " + version in (plugin / "readme.txt").read_text()
out = root / "dist"
out.mkdir(exist_ok=True)
archive = out / (slug + "-" + version + ".zip")
payload = {slug + "/" + name: (plugin / name).read_bytes() for name in (slug + ".php", "readme.txt")}
payload[slug + "/LICENSE"] = (root / "LICENSE").read_bytes()
with zipfile.ZipFile(archive, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as bundle:
    for name, data in sorted(payload.items()):
        item = zipfile.ZipInfo(name, date_time=(2000, 1, 1, 0, 0, 0))
        item.create_system = 3
        item.external_attr = 0o100644 << 16
        item.compress_type = zipfile.ZIP_DEFLATED
        bundle.writestr(item, data)
with zipfile.ZipFile(archive) as bundle:
    assert bundle.testzip() is None
    assert set(bundle.namelist()) == set(payload)
    for name, data in payload.items():
        assert bundle.read(name) == data
checksum = hashlib.sha256(archive.read_bytes()).hexdigest()
(out / "SHA256SUMS").write_text(checksum + "  " + archive.name + "\n")
print(str(archive))
print("SHA256 " + checksum)
