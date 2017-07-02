#!/bin/bash
curl http://metadata.google.internal/computeMetadata/v1/project/project-id -H "Metadata-Flavor: Google" 2>&1 | egrep -oe '[a-zA-Z0-9_.-]+$' | tail -n 1
exit 0
