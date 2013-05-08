Summary
-------
The ActiveConfig module activates after Magento has loaded all of the system.xml files. It looks through the loaded config for any sections that have an import specification. It then fires an event for the module whose config is to be imported to handle.

Config Path
------------
The config path is required to retrieve configuration that was saved
from fields generated using the ActiveConfig module.

The config path is a string that becomes the base of the path string.
It is of the following format:

> module_config_section_name/module_config_group_name

For example the config path 'testsection/testgroup' matches the following config in the system.xml:
><config>
    <sections>
        <testsection>
            <groups>
                <testgroup>
                    <activeconfig_import>
                        ...



Import Specification
--------------------
The import specification is expected to have the following structure:

><config>
    <sections>
      ...
      <groups>
        ...
        <fields>
          <activeconfig_import> <!--signals that an import is necessary -->
            <module>            <!--module whose feature config we want to add-->
                ...             <!-- magento settings (sort_order, show_in_*)-->
              <feature/>        <!--feature whose config we're importing -->
              <feature2>
                ...             <!-- magento settings (sort_order, show_in_*)-->
              </feature2>
            </module>
          </activeconfig_import>
        </fields>
      </groups>
    </sections>

The order of the the import specification within the fields node doesn't matter
so the following two examples are equivalent.

><fields>
	<some_field>...</some_field>
	<activeconfig_import/>...</activeconfig_import>
</fields>

> <fields>
	<activeconfig_import>...</activeconfig_import>
	<some_field>...</some_field>
</fields>

The following settings are planned to be supported
- sort_order
- show_in_default
- show_in_website
- show_in_store

Events
------
The ActiveConfig module listens for the the 
'adminhtml_init_system_config' event.

The events that ActiveConfig fire are taken straight from
the import spec.

in the above configuration, the event fired would
be:
> activeconfig_import_module

as such, modules that support importing config define an event handler for its respective event.