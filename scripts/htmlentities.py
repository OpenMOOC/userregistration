#!/usr/bin/python
# -*- coding: utf-8 -*-

import sys

import HTMLParser

h = HTMLParser.HTMLParser()

phrase = u' '.join(sys.argv[1:])
phrase = phrase.encode("ascii", "ignore")
print h.unescape(phrase)


