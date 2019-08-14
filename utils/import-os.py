#!/usr/bin/env python3

import argparse
import json
import os
import sqlite3
import sys

def main():
	arg_parser = argparse.ArgumentParser(description='Import IQRF OS patch files')
	arg_parser.add_argument('path', type=str, help='IQRF OS patch directory path')
	args = arg_parser.parse_args()

	db_conn = sqlite3.connect(args.path + '/../os.db')
	db_curs = db_conn.cursor()

	db_curs.execute(
		'''CREATE TABLE IF NOT EXISTS os_patches (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		module_type TEXT NOT NULL,
		from_version TEXT NOT NULL,
		from_build TEXT NOT NULL,
		to_version TEXT NOT NULL,
		to_build TEXT NOT NULL,
		part INTEGER NOT NULL,
		parts INTEGER NOT NULL,
		filename TEXT NOT NULL UNIQUE
		);'''
	)
	db_conn.commit()

	files = os.listdir(args.path)
	files.sort()
	for file in files:
		arr = os.path.splitext(file)[0].split('-')
		if 4 < len(arr):
			parts = arr[4].split('of')
		else:
			parts = [1, 1]
		db_curs.execute('''INSERT OR IGNORE INTO os_patches (
			module_type,from_version,from_build,to_version,to_build,part,parts,filename
			) VALUES (?,?,?,?,?,?,?,?);''',
			(arr[1], arr[2][:3], arr[2][4:8], arr[3][:3], arr[3][4:8], parts[0], parts[1], file)
		)
		print('File ' + file + ' has been imported.')

	db_conn.commit()
	db_conn.close()

	return 0

if __name__ == '__main__':
    sys.exit(main())
