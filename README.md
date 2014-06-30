# Aoe_AsyncCache

## Overview

Aoe_AsyncCache extends the Magento cache management in a way that cache-purges are collected and queued instead of directly purged.

A cronjob runs automatically and purges the cache in a given interval, e.g. once per hour.

This extension speeds up Magento in situations where the cache gets flushed quite often.

### Important

Blocking standard cache-flushed means that there might be old cached data still used instead of up to date data. Please keep this in mind when developing and running in production and adjust the cronjob for your needs.
