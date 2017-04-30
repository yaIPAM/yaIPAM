Scan smarty templates for translations:

find theme/default/html/ -name '*.html' | xargs vendor/smarty-gettext/smarty-gettext/tssmarty2c.php -o lang/messages.pot
