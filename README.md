# Symfony Assets v.1.0.6

A list of PHP classes that will help you develop fast & easy various API's.

## Symfony services

`Simfony Assets` provides multiple services. Here is the list of all available services:

- Download Service.
- Export Service.
- Exception Listener.

Just add the following lines inside `app/config/services.yml`:
 
```yaml
    mindlahus.v1.download_service:
        class: Mindlahus\SymfonyAssets\Service\DownloadService
        arguments: ["@service_container"]
    mindlahus.v1.export_service:
        class: Mindlahus\SymfonyAssets\Service\ExportService
        arguments: ["@service_container"]
    mindlahus.v1.exception_listener:
            class: Mindlahus\SymfonyAssets\EventListener\ExceptionListener
            tags:
                - { name: kernel.event_listener, event: kernel.exception, method: onKernelException }
```

## Integrating `gedmo/doctrine-extensions`

The easiest way of connecting this to you app is by simple copy/paste. See `src/Resources/config/doctrine_extensions.yml`.

```yaml
    gedmo.listener.timestampable:
        class: Gedmo\Timestampable\TimestampableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ]

    gedmo.listener.blameable:
        class: Gedmo\Blameable\BlameableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ]
```

In case you use `Auth0` and still want to make use of `blameable` a solution is to using the `DoctrineExtensionListener`.  
This will help you connect your local `User` to the `user profile` made available by `Auth0` after login.

To connect the listener to your app, just `copy/paste` the following snippet.

```yaml
    # KernelRequest listener
    extension.listener:
        class: Mindlahus\SymfonyAssets\Listener\DoctrineExtensionListener
        calls:
            - [ setContainer, [ "@service_container" ] ]
        tags:
            # loggable hooks user username if one is in security context
            - { name: kernel.event_listener, event: kernel.request, method: onKernelRequest }
```

**REMEMBER!** To set the default value of the private variable `$userRepository` inside `DoctrineExtensionListener`.  
This should be a valid `User` entity.

If both cases apply to you, just link to `src/Resources/config/doctrine_extensions.yml` from inside your `config.yml`.  
Please avoid doing this. It does not give you both control or flexibility.