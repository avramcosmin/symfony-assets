# Symfony Assets v.1.0.3

A list of PHP classes that will help you develop fast & easy various API's.

## Symfony services

`Simfony Assets` provides multiple services. Here is the list of all available services:

- Download Service.
- Export Service.

Just add the following lines inside `app/config/services.yml`:
 
```yaml
    mindlahus.v1.download_service:
        class: Mindlahus\SymfonyAssets\Service\DownloadService
        arguments: ["@service_container"]
    mindlahus.v1.export_service:
        class: Mindlahus\SymfonyAssets\Service\ExportService
        arguments: ["@service_container"]
```