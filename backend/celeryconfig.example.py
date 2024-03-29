# List of modules to import when the Celery worker starts.
imports = ("refill.tasks",)

# Broker settings.
broker_url = "redis://redis"

# Using the database to store task state and results.
result_backend = "redis://redis"

# Autoscaling
max_concurrency = 100
min_concurrency = 10

task_annotations = {"*": {"rate_limit": "5/s"}}
