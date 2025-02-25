# ujamii/neos-cobot

<img src="Documentation/assets/neos-cobot.png" width="200px" alt="neos cobot" />

This package integrates the [Cobot for NEOS CMS](https://cobot.ujamii.com). It offers text and image generation with the OpenAI API and the Flux API.

# Installation

To install the package, you can use composer. Run the following command in your terminal:
```bash
composer require ujamii/neos-cobot
```
After the installation, you need to run the following command to update your database schema:
```bash
./flow doctrine:update
```

# Configuration

You need to add the API key for the Cobot API to your `Settings.yaml`.
You get the API key from your Cobot [account settings](https://cobot.ujamii.com/app/api-key).

```yaml
# Configuration/Settings.yaml
Ujamii:
  Cobot:
    services:
      # The API key for the Cobot API
      # You can get it from your Cobot account settings
      apiKey: <your-api-key>
```

# Usage

## Text Generation
To enable **Cobot Text Generation** functionality within your node, add the following configuration to your YAML file.
This will display the Cobot inline editing options directly in the **RichTextToolbar**, allowing for AI-powered content generation within text fields.

```yaml
Neos.NodeTypes:Text:
    properties:
      text:
        ui:
          inline:
            formatting:
              cobot: true
```

## Image Generation

The **Cobot Image Generation** feature is enabled by default, displaying the ImageGeneration button in **all image editors**.
If you prefer to disable this feature, you can add the following configuration to your YAML file and set enabled to false.
```yaml
Ujamii:
  Cobot:
    ImageEditorExtension:
      enabled: true
```

## Alternative Text Generation

If you create alt texts in the backend, these are saved in an extra entity.
To use these alt texts and bring them to the frontend, you must implement an eel helper
for this. Here is an example of how you can do this:

```
<Neos.Neos:ImageTag asset={asset} attributes.alt={ImageExtension.getAltText(asset)} />
```

The method ``ImageExtension.getAltText`` is an eel helper that accepts an ``ImageInterface`` and returns the alt text.

**Disable the alt text generation**<br/>
If you not want to use the alt text generation, you can disable it in the settings:

```yaml
Neos:
  Neos:
    modules:
      management:
          submodules:
              altTextModule:
                enabled: false
```
