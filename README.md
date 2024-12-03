# Ujamii.Cobot

<img src="Documentation/assets/neos-cobot.png" width="200px" alt="neos cobot" />

This package integrates the [Cobot for NEOS CMS](https://cobot.ujamii.com). It offers text and image generation with the OpenAI API and the Flux API.

# Installation

```bash
composer require ujamii/neos-cobot
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
