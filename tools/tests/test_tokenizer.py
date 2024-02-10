"""Unit and regression tests for tokenizer.py"""

import os
import sys
import unittest

current = os.path.dirname(os.path.realpath(__file__))
parent = os.path.dirname(current)
sys.path.append(parent)
from tokenizer import loadBook


class TestLoadBook(unittest.TestCase):
    """ Tests for the LoadBook function"""
    def test_load_book_strip_ruby(self):
        """ Test if an epub with reading added on characters using ruby notation are removed"""

        test_file_path = os.path.join(os.path.dirname(__file__), 'data', 'loadbook_removeruby.epub')

        # Path to the file containing the expected content
        expected_file_path = os.path.join(os.path.dirname(__file__), 'data', 'loadbook_removeruby_expected.txt')

        # Read the expected content from the file
        with open(expected_file_path, 'r', encoding='utf-8') as expected_file:
            expected_content = expected_file.read()

        # Call the function with the test file path
        result = loadBook(test_file_path)

        # Assert that the result matches the expected content
        self.assertEqual(result, expected_content)

if __name__ == '__main__':
    unittest.main()
