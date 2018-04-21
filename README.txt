CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended Modules
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

The Double field module provides extensions to Drupal's core Fields. With this
module you can split your fields up into two separate parts.

 * For a full description of the module visit:
   https://www.drupal.org/project/double_field

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/double_field


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

Install the Double field module as you would normally install a contributed
Drupal module. Visit https://www.drupal.org/node/1897420 for further
information.


CONFIGURATION
-------------

    1. Enable the Double field module at Admin > Extend.
    2. When creating a new field on a content type or custom entity type, choose
    'Double field' from the drop-down menu.
    3. On the Field Settings form for the double field, define the two subfields
    as you would with any other field.
    4. Optionally, on the "edit" form for the double field, you may choose
    options for whether or not the subfields are "required" or limit allowed
    values from the collapsable fieldsets "First subfield" and "Second subfield"
    at the bottom of the page.
    5. Additional display options for the double field will be found under
    "Format Settings" (gear icon) in the "Manage Display" form.

For the moment it includes the following sub-field types:

Boolean
Text
Text (long)
Integer
Float
Decimal
Email
Telephone
Date
Url



MAINTAINERS
-----------

 * Ivan (Chi) - https://www.drupal.org/u/chi
