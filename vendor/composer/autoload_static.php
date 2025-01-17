<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0f3dc7881c9d98116091ae98afe4bae9
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'KeyManager\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'KeyManager\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
            1 => __DIR__ . '/../..' . '/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'KeyManager\\API\\Activations' => __DIR__ . '/../..' . '/includes/API/Activations.php',
        'KeyManager\\API\\Controller' => __DIR__ . '/../..' . '/includes/API/Controller.php',
        'KeyManager\\API\\Keys' => __DIR__ . '/../..' . '/includes/API/Keys.php',
        'KeyManager\\Admin\\Admin' => __DIR__ . '/../..' . '/includes/Admin/Admin.php',
        'KeyManager\\Admin\\ListTables\\ActivationsTable' => __DIR__ . '/../..' . '/includes/Admin/ListTables/ActivationsTable.php',
        'KeyManager\\Admin\\ListTables\\GeneratorsTable' => __DIR__ . '/../..' . '/includes/Admin/ListTables/GeneratorsTable.php',
        'KeyManager\\Admin\\ListTables\\KeysTable' => __DIR__ . '/../..' . '/includes/Admin/ListTables/KeysTable.php',
        'KeyManager\\Admin\\ListTables\\ListTable' => __DIR__ . '/../..' . '/includes/Admin/ListTables/ListTable.php',
        'KeyManager\\Admin\\Menus' => __DIR__ . '/../..' . '/includes/Admin/Menus.php',
        'KeyManager\\Admin\\Notices' => __DIR__ . '/../..' . '/includes/Admin/Notices.php',
        'KeyManager\\Admin\\Orders' => __DIR__ . '/../..' . '/includes/Admin/Orders.php',
        'KeyManager\\Admin\\Products' => __DIR__ . '/../..' . '/includes/Admin/Products.php',
        'KeyManager\\Admin\\Requests' => __DIR__ . '/../..' . '/includes/Admin/Requests.php',
        'KeyManager\\Admin\\Settings' => __DIR__ . '/../..' . '/includes/Admin/Settings.php',
        'KeyManager\\Admin\\Utilities' => __DIR__ . '/../..' . '/includes/Admin/Utilities.php',
        'KeyManager\\ByteKit\\Admin\\Flash' => __DIR__ . '/../..' . '/lib/ByteKit/Admin/Flash.php',
        'KeyManager\\ByteKit\\Admin\\Notices' => __DIR__ . '/../..' . '/lib/ByteKit/Admin/Notices.php',
        'KeyManager\\ByteKit\\Admin\\Settings' => __DIR__ . '/../..' . '/lib/ByteKit/Admin/Settings.php',
        'KeyManager\\ByteKit\\Interfaces\\Pluginable' => __DIR__ . '/../..' . '/lib/ByteKit/Interfaces/Pluginable.php',
        'KeyManager\\ByteKit\\Interfaces\\Scriptable' => __DIR__ . '/../..' . '/lib/ByteKit/Interfaces/Scriptable.php',
        'KeyManager\\ByteKit\\Models\\Model' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Model.php',
        'KeyManager\\ByteKit\\Models\\Post' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Post.php',
        'KeyManager\\ByteKit\\Models\\Query' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Query.php',
        'KeyManager\\ByteKit\\Models\\Relations\\BelongsTo' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Relations/BelongsTo.php',
        'KeyManager\\ByteKit\\Models\\Relations\\BelongsToMany' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Relations/BelongsToMany.php',
        'KeyManager\\ByteKit\\Models\\Relations\\HasMany' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Relations/HasMany.php',
        'KeyManager\\ByteKit\\Models\\Relations\\HasOne' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Relations/HasOne.php',
        'KeyManager\\ByteKit\\Models\\Relations\\Relation' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Relations/Relation.php',
        'KeyManager\\ByteKit\\Models\\Traits\\HasAttributes' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Traits/HasAttributes.php',
        'KeyManager\\ByteKit\\Models\\Traits\\HasMetaData' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Traits/HasMetaData.php',
        'KeyManager\\ByteKit\\Models\\Traits\\HasRelations' => __DIR__ . '/../..' . '/lib/ByteKit/Models/Traits/HasRelations.php',
        'KeyManager\\ByteKit\\Plugin' => __DIR__ . '/../..' . '/lib/ByteKit/Plugin.php',
        'KeyManager\\ByteKit\\Scripts' => __DIR__ . '/../..' . '/lib/ByteKit/Scripts.php',
        'KeyManager\\ByteKit\\Services' => __DIR__ . '/../..' . '/lib/ByteKit/Services.php',
        'KeyManager\\ByteKit\\Traits\\HasPlugin' => __DIR__ . '/../..' . '/lib/ByteKit/Traits/HasPlugin.php',
        'KeyManager\\Emails\\CustomerKeys' => __DIR__ . '/../..' . '/includes/Emails/CustomerKeys.php',
        'KeyManager\\Handlers\\Account' => __DIR__ . '/../..' . '/includes/Handlers/Account.php',
        'KeyManager\\Handlers\\Emails' => __DIR__ . '/../..' . '/includes/Handlers/Emails.php',
        'KeyManager\\Handlers\\Keys' => __DIR__ . '/../..' . '/includes/Handlers/Keys.php',
        'KeyManager\\Handlers\\Misc' => __DIR__ . '/../..' . '/includes/Handlers/Misc.php',
        'KeyManager\\Handlers\\Orders' => __DIR__ . '/../..' . '/includes/Handlers/Orders.php',
        'KeyManager\\Handlers\\Shortcodes' => __DIR__ . '/../..' . '/includes/Handlers/Shortcodes.php',
        'KeyManager\\Handlers\\SoftwareAPI' => __DIR__ . '/../..' . '/includes/Handlers/SoftwareAPI.php',
        'KeyManager\\Handlers\\Stocks' => __DIR__ . '/../..' . '/includes/Handlers/Stocks.php',
        'KeyManager\\Installer' => __DIR__ . '/../..' . '/includes/Installer.php',
        'KeyManager\\Models\\Activation' => __DIR__ . '/../..' . '/includes/Models/Activation.php',
        'KeyManager\\Models\\Generator' => __DIR__ . '/../..' . '/includes/Models/Generator.php',
        'KeyManager\\Models\\Key' => __DIR__ . '/../..' . '/includes/Models/Key.php',
        'KeyManager\\Models\\Model' => __DIR__ . '/../..' . '/includes/Models/Model.php',
        'KeyManager\\Plugin' => __DIR__ . '/../..' . '/includes/Plugin.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0f3dc7881c9d98116091ae98afe4bae9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0f3dc7881c9d98116091ae98afe4bae9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0f3dc7881c9d98116091ae98afe4bae9::$classMap;

        }, null, ClassLoader::class);
    }
}
