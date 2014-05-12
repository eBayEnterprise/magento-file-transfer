ActiveConfig
============

The ActiveConfig module is a platform that allows other modules to
dynamically inject configuration fields into the system configuration
of a third module. The ActiveConfig module activates after Magento has loaded all of the system.xml files. It looks through the loaded config for any sections that have an import specification. It then fires an event for the module whose config is to be imported to handle.

For more details, see [ActiveConfig](docs/activeconfig/)

FileTransfer
============

The FileTransfer (FT) module provides simplified mechanisms for transferring files to and from a remote server.

For more details, see [FileTransfer](docs/filetransfer/)

License and Copyright
=====================

Copyright © 2014 eBay Enterprise

Licensed under the terms of the Open Software License v. 3.0 (OSL-3.0). See [LICENSE.md](LICENSE.md) or http://opensource.org/licenses/OSL-3.0 for the full text of the license.

