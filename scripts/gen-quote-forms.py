#!/usr/bin/env python3
"""Generate app/lib/QuoteForms.php from app/seed/sprintings/forms.json (Gravity Forms export).

Run from the repository root:  python3 scripts/gen-quote-forms.py
The PHP file is the source of truth afterwards; edit it directly for small changes.
"""
import html, json, re, os

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
forms = json.load(open(os.path.join(ROOT, 'app/seed/sprintings/forms.json')))

KEYS = {1: 'quote', 2: 'contact', 3: 'product_general', 5: 'paper_size'}
SUBMIT = {'quote': 'Request My Quote', 'contact': 'Send Message'}
# Product form fields → the product's specification group(s) that can suggest values
SUGGEST = {
    'paper_size': ['Popular Sizes'], 'popular_sizes': ['Popular Sizes'], 'paper_stocks': ['Paper Stocks'],
    'print_options': ['Print Options'], 'binding_options': ['Binding Options'], 'specialty_finishes': ['Specialty Finishes'],
    'cover_options': ['Cover Options'], 'materials': ['Materials', 'Materials :', 'Paper Stocks'], 'shapes': ['Shapes'],
    'drill_hole_positions': ['Drill Hole Positions'], 'features': ['Features'], 'no_of_pages': ['No. of Pages'],
    'number_of_pages': ['Number of Pages'], 'print_on': ['Printed On'], 'paper_uses': ['Uses'],
    'roll_label_shipping_rules': ['Roll Label Shipping Rules'],
}
STEP2 = {'project_name', 'quantity', 'size', 'paper', 'color', 'finish'}


def snake(s):
    s = html.unescape(s).lower().replace('.', '')
    return re.sub(r'[^a-z0-9]+', '_', s).strip('_')


def php(v, ind=0):
    pad = '    ' * ind
    if isinstance(v, bool):
        return 'true' if v else 'false'
    if isinstance(v, (int, float)):
        return str(v)
    if isinstance(v, str):
        return "'" + v.replace('\\', '\\\\').replace("'", "\\'") + "'"
    if isinstance(v, list):
        if all(not isinstance(x, (dict, list)) for x in v):
            return '[' + ', '.join(php(x) for x in v) + ']'
        return '[\n' + ''.join(pad + '    ' + php(x, ind + 1) + ',\n' for x in v) + pad + ']'
    if isinstance(v, dict):
        if all(not isinstance(x, (dict, list)) or (isinstance(x, list) and all(not isinstance(y, (dict, list)) for y in x)) for x in v.values()) and len(v) <= 9:
            return '[' + ', '.join(php(k) + ' => ' + php(x) for k, x in v.items()) + ']'
        return '[\n' + ''.join(pad + '    ' + php(k) + ' => ' + php(x, ind + 1) + ',\n' for k, x in v.items()) + pad + ']'
    raise TypeError(v)


defs = {}
for f in forms:
    if not f.get('active'):
        continue  # form #4 is a trashed competition form
    key = KEYS.get(f['id']) or snake(f['title'])
    fields = []
    for fld in f['fields']:
        label = html.unescape(fld['label'])
        t = fld['type']
        req = bool(fld.get('isRequired'))
        desc = html.unescape(fld.get('description') or '')
        if t == 'name' and fld.get('inputs'):
            fields.append({'name': 'first_name', 'label': 'First name', 'type': 'text', 'required': req, 'autocomplete': 'given-name', 'half': True})
            fields.append({'name': 'last_name', 'label': 'Last name', 'type': 'text', 'required': req, 'autocomplete': 'family-name', 'half': True})
            continue
        if t == 'address':
            for n, l, ac, half in [('street_address', 'Street address', 'address-line1', False), ('address_line_2', 'Address line 2', 'address-line2', False),
                                   ('city', 'City', 'address-level2', True), ('state', 'State / Province', 'address-level1', True),
                                   ('zip', 'ZIP / Postal code', 'postal-code', True), ('country', 'Country', 'country-name', True)]:
                fields.append({'name': n, 'label': l, 'type': 'text', 'required': False, 'autocomplete': ac, 'half': half, 'group': 'Address'})
            continue
        d = {'name': snake(label), 'label': label, 'type': {'email': 'email', 'phone': 'tel', 'textarea': 'textarea', 'number': 'number', 'fileupload': 'file'}.get(t, 'text'), 'required': req}
        if t == 'text' and label == 'Name':
            d['name'] = 'name'
            d['autocomplete'] = 'name'
        if t == 'email':
            d['name'] = 'email'; d['autocomplete'] = 'email'; d['half'] = True
        if t == 'phone':
            d['name'] = 'phone'; d['autocomplete'] = 'tel'; d['half'] = True
        if t == 'textarea':
            d['name'] = 'comments' if label == 'Comments' else 'message'
        if t == 'fileupload':
            d['name'] = 'files'; d['label'] = 'Artwork files'
        if label == 'Company Name':
            d['autocomplete'] = 'organization'
        if desc:
            d['help'] = desc
        if d['name'] in SUGGEST and key not in ('quote',):
            d['suggest'] = SUGGEST[d['name']]
        if t == 'text' and key not in ('quote', 'contact') and d['name'] != 'name':
            d['half'] = True
        fields.append(d)
    if key == 'quote':
        for d in fields:
            d['step'] = 3 if d['type'] in ('file', 'textarea') else 2 if d['name'] in STEP2 else 1
            if d['name'] in STEP2:
                d['half'] = True
    admin = next((n for n in f.get('notifications', []) if n['name'] == 'Admin Notification'), {})
    defs[key] = {
        'id': f['id'],
        'title': html.unescape(f['title']),
        'kind': 'quote' if key != 'contact' else 'contact',
        'submit': SUBMIT.get(key, 'Request a Quote'),
        'success': html.unescape(f['confirmations'][0]['message']) if f.get('confirmations') else 'Thank you! We will be in touch shortly.',
        'consent': False,
        'used_on': [u for u in f.get('used_on', []) if u.startswith('/')],
        'fields': fields,
    }

defs['newsletter'] = {
    'id': 0, 'title': 'Sign up for savings', 'kind': 'newsletter', 'submit': 'Sign Up',
    'success': 'Thanks for signing up! Watch your inbox for savings.', 'consent': False, 'used_on': ['footer'],
    'fields': [{'name': 'email', 'label': 'Email', 'type': 'email', 'required': True, 'autocomplete': 'email'}],
}

out = '''<?php
/**
 * Sprintings quote-form definitions, generated from the Gravity Forms export
 * (app/seed/sprintings/forms.json) by scripts/gen-quote-forms.py. Edit here to change a form.
 *
 * Each form: id (Gravity Form ID; 0 = new), title, kind (quote|contact|newsletter), submit label,
 * success message, consent (show a consent checkbox), used_on (pages that used it on the old site)
 * and fields. Field: name (snake_case), label, type (text|email|tel|number|textarea|file),
 * required, and optionally autocomplete, half (half width), help, group, step (wizard step,
 * main quote form only) and suggest (product specification groups that offer suggestions).
 * Products choose their form in Dashboard → Products → Quote form; the default is product_general.
 */
final class QuoteForms
{
    public const DEFAULT_PRODUCT_FORM = 'product_general';

    /** Artwork uploads: accepted extensions => allowed MIME types (checked with finfo). */
    public const UPLOAD_TYPES = [
        'pdf' => ['application/pdf', 'application/x-pdf'],
        'ai' => ['application/pdf', 'application/postscript', 'application/illustrator', 'application/octet-stream'],
        'eps' => ['application/postscript', 'application/eps', 'image/x-eps', 'application/octet-stream'],
        'psd' => ['image/vnd.adobe.photoshop', 'application/x-photoshop', 'image/x-photoshop', 'image/psd', 'application/octet-stream'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'tif' => ['image/tiff'],
        'tiff' => ['image/tiff'],
        'zip' => ['application/zip', 'application/x-zip-compressed', 'application/x-zip'],
    ];
    public const UPLOAD_MAX_BYTES = 50 * 1024 * 1024;
    public const UPLOAD_MAX_FILES = 5;

    public static function all(): array
    {
        return ''' + php(defs, 2) + ''';
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** [key => title] for dashboard selects and the inbox filter. */
    public static function options(): array
    {
        return array_map(fn($f) => $f['title'], self::all());
    }

    /** Key of the form a product uses. */
    public static function forProduct(?array $product): string
    {
        $k = (string)($product['quote_form'] ?? '');
        return $k !== '' && isset(self::all()[$k]) && self::all()[$k]['kind'] === 'quote' && $k !== 'quote' ? $k : self::DEFAULT_PRODUCT_FORM;
    }
}
'''
open(os.path.join(ROOT, 'app/lib/QuoteForms.php'), 'w').write(out)
print('wrote', len(defs), 'forms')
