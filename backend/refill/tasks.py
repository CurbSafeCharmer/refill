import os

import pywikibot
from celery import Celery, states
from celery.exceptions import Ignore
from celery.utils.log import get_task_logger

from .models import Context
from .transforms import FillExternal, FillRef, MergeRef

os.environ.setdefault("PYWIKIBOT2_NO_USER_CONFIG", "1")
os.environ.setdefault("CELERY_CONFIG_MODULE", "celeryconfig")

logging = get_task_logger("refill2")
app = Celery("tasks")
app.config_from_envvar("CELERY_CONFIG_MODULE")


@app.task(bind=True)
def fixWikipage(
    self,
    page: str,
    fam: str = "wikipedia",
    code: str = "en",
    wikicode: str = False,
    preferences: dict = {},
):
    site = pywikibot.Site(fam=fam, code=code)
    page = pywikibot.Page(site, page)

    # Let the exceptions bubble up and cause the task to fail
    if not wikicode:
        wikicode = page.get()

    ctx = Context()
    ctx.attachTask(self)
    ctx.attachPage(page)
    ctx.setPreferences(preferences)
    ctx.transforms = [MergeRef(ctx), FillRef(ctx), MergeRef(ctx), FillExternal(ctx)]
    ctx.applyTransforms(wikicode)

    return ctx.getResult()


@app.task(bind=True, ignore_result=True)
def fail(self):
    self.update_state(state=states.FAILURE)
    raise Ignore()


@app.task(bind=True, ignore_result=True)
def revoke(self):
    self.update_state(state=states.REVOKED)
    raise Ignore()


TASK_MAPPING = {"fixWikipage": fixWikipage, "fail": fail, "revoke": revoke}
