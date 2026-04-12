#!/usr/bin/env bash

set -euo pipefail

echo "Activating venv in $HOME/www/python/venv..."
source $HOME/www/python/venv/bin/activate

echo "Starting scheduler..."
cd $HOME/refill/backend
exec celery beat
