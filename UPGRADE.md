# Ingenico Connect upgrade guide

Since v2.0.0, the namespace of the module has changed from `Netresearch_Epayments`
to `Ingenico_Connect`. This upgrade guide explains what this means for you as a
merchant, and as a developer / implementing 3rd party.

## For merchants

As a merchant, you don't have to worry. All the configuration, orders (pending
and closed ones) and other settings will remain the same. There is no need
for further configuration. The following data will be kept intact:

- Configuration settings of the module
- Email templates
- Inline translations

## For developers / implementing 3rd party

This module is a drop-in replacement for the previous module. However, some
attention is required for this upgrade.

### Uninstalling the previous version

Depending on how you installed the previous version of the module, you need
to follow one of the following uninstallation instructions:

- The previous module was installed from a package using Composer
- The previous module was installed manually

#### The previous module was installed from a package using Composer

First check in your projects' `composer.json` file what is:

- The name of the module (this can be found in the `require`-section, for example `nrepayments/module-epayments-m1`)
- What repository is configured for this module (this can be found in the `repositories`-section)

Then execute the following commands in the shell:

    composer remove [vendor/module-name]
    composer config --unset repositories.[repository-name]

Next, you have to delete the setup entry in the database of the old module. Execute the following query:

    DELETE FROM core_resource WHERE code = "netresearch_epayments_setup"

#### The previous module was installed manually

If you installed the module manually, you need to manually remove the code of the old module
in Magento 1. This can be done by removing the following files and/or folders:

    app/code/community/Netresearch/Epayments
    app/design/frontend/base/default/template/epayments
    app/design/adminhtml/default/default/template/epayments
    app/etc/modules/Netresearch_Epayments.xml
    shell/ingenico_wximport.php
    shell/ingenico_processevents.php
    lib/Ingenico
    app/locale/en_US/template/email/netresearch_epayments/
    js/ingenico_epayments
    app/design/frontend/base/default/layout/ingenico_epayments.xml
    skin/frontend/base/default/css/ingenico_epayments/
    app/design/adminhtml/default/default/layout/ingenico_epayments.xml
    skin/adminhtml/default/default/ingenico_epayments.css
    
Next, you have to delete the setup entry in the database of the old module. Execute the following query:

    DELETE FROM core_resource WHERE code = "netresearch_epayments_setup"

### Installing the new version

See the [readme instructions](README.md) on how to install the new version of the module. 

### Backward compatibility

Since this update includes a namespace change, it is breaking with backward 
compatibility. However, this will only affect you if you did some customization 
with the module. Please check if you did any of the following actions in your 
integration and follow the instructions if so:

- Create a rewrite for an existing class in the `Netresearch_Epayments` namespace.
- Use classes in the `Netresearch_Epayments` namespace in your own code.

If your code applies to any of the scenarios above, please use the following 
instructions to update your code:

#### Create a rewrite for an existing class

Replace the `netresearch_epayments` class prefix by `ingenico_connect` your `config.xml` files:

    // Old situation:
    <config>
        <global>
            <models>
                <netresearch_epayments>
                    <rewrite>
                        <token>Custom_Module_Model_Token</token>
                    </rewrite>
                </netresearch_epayments>
            </models>
        </global>                        
    </config>
    // New situation:
    <config>
        <global>
            <models>
                <ingenico_connect>
                    <rewrite>
                        <token>Custom_Module_Model_Token</token>
                    </rewrite>
                </ingenico_connect>
            </models>
        </global>                        
    </config>

    This also applies to all `netresearch_epayments_*`-nodes, like `netresearch_epayments_resources`, 
    `netresearch_epayments_setup`, etc.

Update your custom code to extend the class from the new namespace:

    // Old situation:
    class Custom_Module_Model_Token extends Netresearch_Epayments_Model_Token { ... }
    // New situation:
    class Custom_Module_Model_Token extends Ingenico_Connect_Model_Token { ... }

#### Use objects with the old class prefix in your own code

Replace the `netresearch_epayments` class prefix by `ingenico_connect` in the magic `Mage::`-methods:

    // Old situation:
    $tokenModel = Mage::getModel('netresearch_epayments/token');
    $helper = Mage::helper('netresearch_epayments');
    // New situation:
    $tokenModel = Mage::getModel('ingenico_connect/token');
    $helper = Mage::helper('ingenico_connect');
