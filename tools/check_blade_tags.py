#!/usr/bin/env python3
import os
import re

ROOT = os.path.join(os.path.dirname(__file__), '..', 'resources', 'views')
TAGS = ['div', 'section', 'header', 'main', 'footer', 'nav', 'ul', 'li', 'form', 'article', 'aside']

def strip_blade(text):
    # remove blade echo and simple directives to reduce false positives
    text = re.sub(r"@\w+(?:\([^\)]*\))?", '', text)
    text = re.sub(r"\{\{\{?.*?\}?\}\}", '', text, flags=re.S)
    return text

def count_tags(text, tag):
    open_re = re.compile(rf"<\s*{tag}(?:\s+[^>/]*)?(?<!/)?>", re.I)
    close_re = re.compile(rf"<\s*/\s*{tag}\s*>", re.I)
    return len(open_re.findall(text)), len(close_re.findall(text))

def main():
    root = os.path.abspath(ROOT)
    problems = []
    total_files = 0
    for dirpath, dirs, files in os.walk(root):
        for f in files:
            if not f.endswith('.blade.php'):
                continue
            total_files += 1
            path = os.path.join(dirpath, f)
            with open(path, 'r', encoding='utf-8', errors='ignore') as fh:
                content = fh.read()
            cleaned = strip_blade(content)
            bad = []
            for tag in TAGS:
                o, c = count_tags(cleaned, tag)
                if o != c:
                    bad.append((tag, o, c))
            if bad:
                problems.append((os.path.relpath(path, start=os.path.abspath(os.path.join(root, '..', '..'))), bad))

    print(f'Checked {total_files} blade files under: {root}')
    if not problems:
        print('No tag count mismatches detected for tags:', ', '.join(TAGS))
        return 0

    print('\nFiles with mismatched tags:')
    for p, issues in problems:
        print('-' * 60)
        print(p)
        for tag, o, c in issues:
            print(f'  <{tag}>: opens={o}, closes={c}')
    print('\nNote: this is a heuristic check; includes and components may affect counts.')
    return 1

if __name__ == '__main__':
    raise SystemExit(main())
