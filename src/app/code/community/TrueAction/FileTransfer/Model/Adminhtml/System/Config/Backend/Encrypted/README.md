# Private Key Configuration

## Exchange Platform Configuration

The private key used to validate a Magento Website is entered via the Exchange Platform Configuration. The key should
copied for its source and pasted into the Private Key field.

## Behavior
### New Key
Upon entry of a new key that is successfully validated, Magento will issue a notice that the New Key has been 
successfully installed.

The Private Key field will always display the corresponding *public* key to allow the administrator to verify that a
key has been entered, and that it is valid.

### Invalid Key
The system will not allow an invalid key to be stored. An error will be issued to the admin panel. 

If there was a valid key already installed, the system will revert to that key an issue a warning to the admin panel.

### Inadvertent Entry 
Is treated as an Invalid Key - the previous value is simply restored.

### Deleting a Key
You cannot delete a key.
