<template>
  <v-col md="8" offset-md="2" lg="6" offset-lg="3" class="align-self-start">
    <h1>{{ msg('result') }}</h1>

    <v-card class="progress-card">
      <v-card-text>
        <div class="icon-wrapper">
          <v-progress-circular
            indeterminate
            color="primary"
            v-if="!loaded || running"
          ></v-progress-circular>
          <span v-else-if="state == 'PENDING'" class="material-icons">hourglass_empty</span>
          <span v-else-if="state == 'SUCCESS'" class="material-icons successful">done</span>
          <span v-else-if="state == 'FAILURE'" class="material-icons unsuccessful">error</span>
          <span v-else-if="state == 'REVOKED'" class="material-icons unsuccessful">cancel</span>
          <span v-else-if="state == 'BACKEND_ERROR'" class="material-icons unsuccessful">cloud_off</span>
          <span v-else class="material-icons">help</span>
        </div>
        <div v-if="loaded" class="progress-wrapper">
          {{ msg('state-' + state) }}
          <div v-if="state != 'SUCCESS'" class="progress-description">{{ msg('state-' + state + '-description') }}</div>
        </div>
      </v-card-text>
      <v-progress-linear v-if="loaded" v-model="progressBar" class="mt-0"></v-progress-linear>
    </v-card>

    <div v-if="loaded">
      <v-card v-if="errors.length > 0">
        <v-card-text>
          {{ msg('errors') }}
        </v-card-text>
      </v-card>

      <div ref="diff" v-if="origWikicode && wikicode" v-html="diff">
      </div>
      <v-textarea
        v-model="wikicode"
        variant="outlined"
      ></v-textarea>

      <ChangeDetails ref="changeDialog" :changes="changes" :taskName="taskName" :taskId="taskId"/>
      <ErrorDetails ref="errorDialog" :errors="errors"/>

      <v-card class="action-card">
        <v-card-actions>
          <span class="tip">{{ msg('chancetoreview') }}</span>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="savePage" :disabled="!wikiAction">{{ msg('previewandsave') }}</v-btn>
          <v-btn icon @click="showTaskInfo = !showTaskInfo">
            <span class="material-icons">keyboard_arrow_down</span>
          </v-btn>
        </v-card-actions>
        <v-slide-y-transition>
          <v-card-text v-show="showTaskInfo">
            <h2>{{ msg('taskinfo') }}</h2>
            <ul>
              <li>{{ msg('taskinfo-name') }}: {{ taskName }}</li>
              <li>{{ msg('taskinfo-id') }}: {{ taskId }}</li>
              <li>{{ msg('taskinfo-state') }}: {{ state }}</li>
              <li>{{ msg('taskinfo-percentage') }}: {{ percentage }}</li>
              <li>{{ msg('taskinfo-running') }}: {{ running }}</li>
              <li>{{ msg('taskinfo-submiturl') }}: {{ wikiAction }}</li>
            </ul>
          </v-card-text>
        </v-slide-y-transition>
      </v-card>
    </div>
    <form class="fake-editform" ref="form" name="editform" method="post" v-bind:action="wikiAction" target="_blank">
      <textarea type="hidden" name="wpTextbox1">{{ wikicode }}</textarea>
      <input type="hidden" name="wpAutoSummary" value="fakehash">
      <input type="hidden" name="wpSummary" v-bind:value="summary">
      <input type="hidden" name="wpStarttime" v-bind:value="startTime">
      <input type="hidden" name="wpEdittime" v-bind:value="editTime">
      <input type="hidden" name="wpDiff" value="Show changes">
      <input type="hidden" name="wpWatchthis" value="n">
      <input type="hidden" name="wpUltimateParam" value="1">
    </form>
  </v-col>
</template>
<script>
import oboe from 'oboe';
//import get from 'lodash/get';
//import isEmpty from 'lodash/isEmpty';

// Shim for lodash functions
const get = (obj, path, defaultValue = undefined) => {
  const travel = regexp =>
    String.prototype.split
    .call(path, regexp)
    .filter(Boolean)
    .reduce((res, key) => (res !== null && res !== undefined ? res[key] : res), obj);
  const result = travel(/[,[\]]+?/) || travel(/[,[\].]+?/);
  return result === undefined || result === obj ? defaultValue : result;
};
const isEmpty = obj => [Object, Array].includes((obj || {}).constructor) && !Object.entries((obj || {})).length;

import { WikEdDiff } from 'wdiff';
import URI from 'urijs';
import ChangeDetails from '../components/ChangeDetails';
import ErrorDetails from '../components/ErrorDetails';

export default {
  components: {
    ChangeDetails,
    ErrorDetails,
  },
  data() {
    return {
      loaded: false,
      running: false,
      state: 'UNKNOWN',
      percentage: 0,
      stepPercentage: 0,
      taskName: 'unknown',
      taskId: '',
      task: {},
      errors: [],
      changes: [],
      wikicode: '',
      markedWikicode: '',
      origWikicode: '',
      diff: '',
      summary: '',
      startTime: '',
      editTime: '',
      wikiAction: '',
      showTaskInfo: false,
    }
  },
  computed: {
    progressBar() {
      return this.stepPercentage * 100;
    },
  },
  created() {
    this.fetchData();
  },
  mounted() {
    window.refillChangeClickHandler = (id) => {
      this.$refs.changeDialog.review(id);
    }
    window.refillErrorClickHandler = (id) => {
      this.$refs.errorDialog.review(id);
    }
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    fetchData () {
      this.loaded = false;
      this.taskId = this.$route.params.taskId;
      this.taskName = this.$route.params.taskName;
      this.origWikitext = '';

      // Stream status with Oboe.js
      oboe({
        url: this.$config.api + '/statusStream/' + this.taskName + '/' + this.taskId
      })
      .node('{state info}', (node) => {
        this.loaded = true;
        this.task = node;
        this.state = this.task.state;
        switch (this.state) {
          case 'PROGRESS':
            // Show progress for the whole task and the current step
            let stepPercentage = get(this.task.info.transforms,
              [this.task.info.overall.currentTransform, 'percentage']
            )
            if (stepPercentage) {
              this.stepPercentage = stepPercentage;
            }

            this.percentage = this.task.info.overall.percentage;
            this.running = true;
            break;
          case 'SUCCESS':
            this.percentage = 1;
            this.stepPercentage = 1;
            this.running = false;
            break;
          case 'FAILURE':
          case 'REVOKED':
          case 'PENDING':
            this.running = false;
            return;
        }

        // Show result and compute diff
        this.changes = this.task.info.changes;
        this.errors = this.task.info.errors;
        this.wikicode = this.task.info.wikicode;
        this.markedWikicode = this.task.info.markedWikicode;
        let origWikicode = get(this.task, 'info.origWikicode');
        if (origWikicode) {
          this.origWikicode = origWikicode;
        }

        // HACK: this works around the error "Uncaught Error: wdiff__WEBPACK_IMPORTED_MODULE_1__.WikEdDiff is not a constructor", which is related to loading the libs/wdiff.js library in webpack
        // let wdiff = new WikEdDiff();
        let wdiff = { diff: () => '' };
        this.diff = wdiff.diff(this.origWikicode, this.markedWikicode);

        // Construct fake edit form
        let wikipage = get(this.task, 'info.wikipage');
        if (!isEmpty(wikipage)) {
          this.wikiAction = URI(wikipage.path)
            .domain(wikipage.domain)
            .protocol(wikipage.protocol)
            .query({
              'title': wikipage.upage,
              'action': 'submit'
            })
            .toString();
          this.editTime = wikipage.editTime;
          this.startTime = wikipage.startTime;
        }

        // Generate edit summary
        // FIXME: Use `toollink`
        let fillCount = get(this.task, 'info.transforms.FillRef.metadata.count', 0);
        this.summary = this.msg('summary', fillCount, 0, 'reFill 2');
      })
      .done((json) => {
        if (this.state == 'PROGRESS') {
          console.log('Not done yet - Initiating another request');
          this.fetchData();
        }
      })
      .fail(() => {
        this.loaded = true;
        this.running = false;
        this.state = 'BACKEND_ERROR';
      });
    },
    savePage() {
      this.$refs.form.submit();
    }
  }
}
</script>
<style lang="scss" scoped>
.md-input-container textarea {
  transition: all 0s;
  font-family: monospace !important;
  min-height: 400px;
  overflow: scroll !important;
}
.icon-wrapper {
  position: relative;
  display: inline-block;
  padding: 0;
  width: 50px;
  height: 50px;
  line-height: 50px;
  vertical-align: top;

  .icon {
    position: absolute;
    font-size: 50px;
    line-height: 50px;
  }
  .successful {
    color: green;
  }
  .unsuccessful {
    color: red;
  }
}
.progress-wrapper {
  position: relative;
  display: inline-block;
  font-size: 20px;
  line-height: 50px;
  vertical-align: top;
}
.progress-description {
  font-size: 15px;
}
.fake-editform {
  display: none;
}
.action-card {
  padding-left: 8px;
  .tip {
    margin-right: 8px;
  }
}
</style>
<style lang="scss">
/* Materialize */
.wikEdDiffFragment {
  box-shadow: 0 1px 5px rgba(0,0,0,.2), 0 2px 2px rgba(0,0,0,.14), 0 3px 1px -2px rgba(0,0,0,.12) !important;
  border-radius: 2px !important;
  border: none !important;
}
</style>
