#!/usr/bin/env python3
"""
Push attendance punches from a CSV file to BioAttent API.
Use when Mivanta mBio-G1 or PayTime exports logs to CSV.

Usage:
  pip install requests
  cp config.example.json config.json
  python sync_to_biotime.py punches.csv
"""

import csv
import json
import sys
from datetime import datetime
from pathlib import Path

try:
    import requests
except ImportError:
    print("Install requests: pip install requests")
    sys.exit(1)

CONFIG_PATH = Path(__file__).resolve().parent / "config.json"


def load_config():
    if not CONFIG_PATH.exists():
        print("Create config.json from config.example.json and set api_url and device_key.")
        sys.exit(1)
    with open(CONFIG_PATH, "r", encoding="utf-8") as f:
        return json.load(f)


def parse_time(value):
    value = (value or "").strip()
    if not value:
        return None
    for fmt in (
        "%Y-%m-%d %H:%M:%S",
        "%Y-%m-%d %H:%M",
        "%d/%m/%Y %H:%M:%S",
        "%d-%m-%Y %H:%M:%S",
        "%Y-%m-%dT%H:%M:%S",
    ):
        try:
            dt = datetime.strptime(value.replace("Z", "").strip(), fmt)
            return dt.strftime("%Y-%m-%d %H:%M:%S")
        except ValueError:
            continue
    return value


def run(csv_path):
    config = load_config()
    api_url = config.get("api_url", "").rstrip("/")
    device_key = config.get("device_key", "")
    if not api_url or not device_key:
        print("Set api_url and device_key in config.json")
        sys.exit(1)

    col_map = config.get("csv_columns", {})
    id_col = col_map.get("device_user_id", "device_user_id")
    time_col = col_map.get("punch_time", "punch_time")
    encoding = config.get("csv_encoding", "utf-8")

    path = Path(csv_path)
    if not path.exists():
        print("File not found:", path)
        sys.exit(1)

    punches = []
    with open(path, "r", encoding=encoding, newline="") as f:
        reader = csv.DictReader(f)
        for row in reader:
            uid = row.get(id_col) or row.get("userId") or row.get("user_id")
            pt = row.get(time_col) or row.get("dateTime") or row.get("timestamp")
            if not uid or not pt:
                continue
            punch_time = parse_time(pt)
            if not punch_time:
                continue
            punches.append({
                "device_user_id": str(uid).strip(),
                "punch_time": punch_time,
                "punch_type": row.get("punch_type") or row.get("type"),
            })

    if not punches:
        print("No valid rows in CSV")
        return

    headers = {"Content-Type": "application/json", "X-Device-Key": device_key}
    try:
        r = requests.post(api_url, json={"punches": punches}, headers=headers, timeout=30)
        data = r.json() if "application/json" in r.headers.get("content-type", "") else {}
        if r.ok and data.get("success"):
            print("Synced:", data.get("inserted", 0), "punches")
        else:
            print("Error", r.status_code, data.get("message", r.text))
            sys.exit(1)
    except requests.RequestException as e:
        print("Request failed:", e)
        sys.exit(1)


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python sync_to_biotime.py <punches.csv>")
        sys.exit(1)
    run(sys.argv[1])
