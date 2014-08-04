[Contributing to This Project](CONTRIBUTING.md)

FileTransfer
============

The FileTransfer (FT) module provides simplified mechanisms for transferring files to and from a remote server.

The module allows for one or more remote hosts to be configured by other modules. Each configured remote host may contains up to two sets of local-to-remote directory configurations. One set for files to be transferred from a remote directory to the local directory and one for files to be transferred from the local directory to a remote directory.

When run, the FT module will transfer all files in the configured remote directories to configured local directories and all files in configured local directories to configured remote directories. After completing each set of transfers, the FT module will dispatch an event allowing interested modules to process and respond to newly imported or exported files.

The module currently only supports transferring files via SFTP but support for additional protocols may be added as needed.

## Dependencies

Requires the eBayEnterprise/magento-active-config module for registering configuration that can be injected into other modules.

## Configuration

Any module using FileTransfer _must_ add two pieces of configuration:

1. Configuration for the remote server the transfers will be to and from.
2. Register the remote server configuration with FileTransfer.

### Remote Host Configuration

The FT module must be provided certain configuration to allow the module to connect to a remote server and get or put files there. This configuration consists of two parts:

1. Information on how to connect to the remote server.
2. Details on which files should be retrieved from or put onto the remote server.

#### Connection Details

The FT module must be provided with details for connecting to the remote server. The FT module has been configured to provide all necessary configuration via the ActiveConfig module.

Include FT configuration in a `my_module` module's system.xml configuration, adding all necessary configuration to the Magento admin.

```xml
<!-- My/Module/etc/system.xml -->
<config>
	...
	<sections>
		<my_module>
			...
			<groups>
				<my_group>
					...
					<fields>
						...
						<activeconfig_import>
							<filetransfer>
								<!-- these can be set as needed by my_module -->
								<sort_order>10</sort_order>
								<show_in_store>0</show_in_store>
								<show_in_website>0</show_in_website>
								<show_in_default>1</show_in_default>
							</filetransfer>
						</activeconfig_import>
						...
					</fields>
				</my_group>
			</groups>
		</my_module>
	</sections>
</config>
```

The config paths for the added fields will be relative to the path configured in the system.xml. In the example above, the paths will all be relative to `my_module/my_group`. Default values can be included at the specified path within the module's config.xml.

For example, to set the default FileTransfer protocol to SFTP for the module configured above, the following could be added to the module's config.xml:

```xml
<config>
	<default>
		<my_module>
			<my_group>
				<filetransfer_protocol>SFTP</filetransfer_protocol>
			</my_group>
		</my_module>
	</default>
</config>
```

Currently, the FileTransfer module provides the following configuration fields via ActiveConfig:

* filetransfer_protocol: The protocol to use to make the transfers. Currently, only SFTP is supported.
* filetransfer_sftp_username: The user name to authenticate with.
* filetransfer_sftp_auth_type: The type of authentication to use. Currently, password or private key authentication are supported.
* filetransfer_sftp_password: The password to authenticate with.
* filetransfer_sftp_ssh_key_file: The name of the private key file. This field is presented as a file select in the admin, allowing administrators to upload the private key file.
* filetransfer_sftp_host: The host name of the remote host.
* filetransfer_sftp_port: The port to connect to on the remote host.

As support for additional protocols is added, additional configuration fields may be included.

#### Directory Details

Modules using the FT module may specify pairs of local and remote directories where the FT module will look when importing and exporting files. If used, this configuration _must_ be included with the configuration for the [connection details](#connection-details). These pairings are split into two groups in the configuration.

* `filetransfer_imports`: Files which should be transferred from the remote directory to the local directory.
* `filetransfer_exports`: Files which should be transferred from the local directory to the remote directory.

For each set of configuration, any number of unique child nodes may be added. Each child node represents one pairing of a local to remote directory. These nodes _must_ contain the following:

* `local_directory`: The path to the directory on the local file system, relative to the Magento base `var` directory.
* `remote_directory`: The path to the directory on the remote host.

Additionally, any export nodes _must_ also contain the following:

* `sent_directory`: Local directory files in the outbox will be moved to after being successfully sent, this prevents files from being resent on successive runs of the export. This directory will be relative to the Magento base `var` directory.

Optionally, each node may also contain the following:

* `file_pattern`: A shell glob matching files that should be transferred, defaults to `*`.

Sample configuration:

```xml
<!-- MyModule/etc/config.xml -->
<config>
	<default>
		<my_module>
			...
			<!-- node encapsulating all FT configuration -->
			<my_group>
				<!-- FT defaults or any other module specific config -->
				<filetransfer_protocol>sftp</filetransfer_protocol>
				<!-- files to import -->
				<filetransfer_imports>
					<!--
					child nodes only need to be unique to this remote host
					import configuration
					-->
					<product>
						<!-- relative to Mage::getBaseDir('var') -->
						<local_directory>MyModule/inbox</local_directory>
						<remote_directory>/RemoteHost/outbox</remote_directory>
						<file_pattern>*product_file*.xml</file_pattern>
					</product>
					<price>
						<local_directory>MyModule/special_var/price/inbox</local_directory>
						<!--
						only files matching this pattern will be transferred, so this
						attempt to transfer the same files as the 'product' import
						-->
						<remote_directory>/RemoteHost/outbox</remote_direcory>
						<file_pattern>*price_file*.xml</file_pattern>
						<!--
						perfectly safe to put other configuration here the module
						may need in addition to the FT configuration
						-->
						<some_special_value>2</some_special_value>
					</price>
				</filetransfer_imports>
				<filetransfer_exports>
					<!--
					child nodes only need to be unique to this remote host
					export configuration, so the 'product' node here will not collide
					with the 'product' node in the import
					-->
					<product>
						<local_directory>MyModule/outbox</local_directory>
						<remote_directory>/RemoteHost/inbox</remote_host>
						<!-- relative to Mage::getBaseDir('var') -->
						<sent_directory>MyModule/sent</sent_directory>
						<!-- will only send files matching this pattern -->
						<file_pattern>*product_file*.xml</file_pattern>
					</product>
				</filetransfer_exports>
			</my_group>
		</my_module>
	</default>
</config>
```

### Config Registration

Any module adding configuration for FileTransfer, must make the FT module aware of it. This is done by adding a some additional config.xml the FileTransfer module is aware of.

Modules _must_ include a unique config node located in a `config/default/filetransfer/registry` node. The value of the node should be the config path (slash delimited node names) to the node containing the configuration added by the module for the [remote host](#remote-host-configuration).

Using the configuration in previous examples, the config registration for the MyModule would be:

```xml
<!-- MyModule/etc/config.xml -->
<config>
	<default>
		<filetransfer>
			<registry>
				<!--
				The node name must be unique to all modules using FT. The config path
				for value points to the configuration setup in the previous examples.
				-->
				<my_module_my_group>my_module/my_group/feed</my_module_my_group>
				<!-- can register more than one remote host configuration -->
				<my_module_another_group>my_module/another_group/another_feed</my_module_another_group>
			</registry>
		</filetransfer>
	</default>
</config>
```

## Order of Transfers

The FileTransfer module will perform all imports, remote to local transfers, before all exports, local to remote transfers. No further guarantee is made as to the order in which individual directories of files are processed.

### Import Process

When importing files, FileTransfer will perform the following steps in order:

1. Collect configuration registered for any remote hosts.
2. Get the configuration for a single remote host that has at least one valid `filetransfer_imports` configured.
3. Create and authenticate a connection to the remote host if necessary.
4. For each `filetransfer_imports` directory pairing, get all files in the `remote_directory`, optionally matching the configured `file_pattern` and copy them to the configured `local_directory`.
5. Delete any file successfully retrieved from the remote.
6. Repeat steps 2-5 for all configured remote hosts.
7. Dispatch the `filetransfer_import_complete` event.

### Export Process

When exporting files, FileTransfer will perform the following steps in order:

1. Collection configuration registered for any remote host.
2. Get the configuration for a single remote host that has at least one valid `filetransfer_exports` configured.
3. Create and authenticate a connection to the remote host if necessary.
4. For each `filetransfer_exports` directory pairing, get all local files in the `local_directory`, optionally matching the `file_pattern`, and copy them to the configured `remote_directory`.
5. Move any successfully transfered files to the configured `sent_directory`.
6. Repeat steps 2-5 for all configured remote hosts.
7. Dispatch the `filetransfer_export_complete` event.

## Events

The FileTransfer module dispatches two events. These events can be used by interested modules to be notified when the FT module has completed getting or sending files.

### filetransfer_import_complete

_Event Parameters: none_

This even will be dispatched after the FT module has completed transferring all files found on all configured remote hosts to the local directories. This even will _always_ be dispatched, even if no files were transferred.

### filetransfer_export_complete

_Event Parameters: none_

This event will be dispatched after the FT module has completed sending all files found in all configured local directories to the remote hosts. This event will _always_ be dispatched, even if no files were transferred.

License and Copyright
=====================

Copyright © 2014 eBay Enterprise

Licensed under the terms of the Open Software License v. 3.0 (OSL-3.0). See [LICENSE.md](LICENSE.md) or http://opensource.org/licenses/OSL-3.0 for the full text of the license.

