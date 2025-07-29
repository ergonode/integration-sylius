# Installation
1. Add package to your project via composer:
    ```bash
    composer require bitbag/sylius-ergonode-plugin --no-scripts
    ```
2. Add required environment variables to your `.env` file:
    ```dotenv
    ADMIN_MENU_ERGONODE_SECTION_POSITION=5
    ERGONODE_API_KEY_ENCRYPTION_KEY_PATH='%kernel.project_dir%/config/encryption/ergonode_encryption_key.dev'
    ERGONODE_REQUEST_TIMEOUT_PERIOD=10
    ERGONODE_INTEGRATION_MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
    ```
3. Make sure that plugin is registered in `config/bundles.php` file.
    ```php
    return [
        ...
            BitBag\SyliusErgonodePlugin\BitBagSyliusErgonodePlugin::class => ['all' => true],
        ...
    ];
    ```
4. Import required config in your `config/packages/_sylius.yaml` file:
    ```yaml
    imports:
        ...
        - { resource: "@BitBagSyliusErgonodePlugin/config/config.yaml" }
        ...
    ```

5. Import routes in your `config/routes.yaml` file:
    ```yaml
    bitbag_sylius_ergonode_shop:
        resource: "@BitBagSyliusErgonodePlugin/config/shop_routing.yaml"
        prefix: /{_locale}
        requirements:
            _locale: ^[a-z]{2}(?:_[A-Z]{2})?$
    
    bitbag_sylius_ergonode_admin:
        resource: "@BitBagSyliusErgonodePlugin/config/admin_routing.yaml"
        prefix: /admin
    ```

6. Run migrations:
    ```shell
      php bin/console doctrine:migrations:migrate
    ```

6. Load plugin fixtures (optional):
    ```shell
      php bin/console sylius:fixtures:load bitbag_sylius_ergonode_plugin
    ```
