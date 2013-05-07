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

> <fields>
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