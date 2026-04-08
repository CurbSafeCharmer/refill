<template>
  <v-dialog
    v-model="dialog"
    width="800px"
  >
    <v-card v-if="dialog">
      <v-app-bar color="primary" dark>
        <div class="v-toolbar__title">{{ msg('error-details') }}</div>
      </v-app-bar>
      <div class="shade">
        {{ msg('error-type-' + errors[id].type) }}
      </div>
      <v-card-text>
        {{ msg('error-transform-' + errors[id].transform) }}
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  props: ['errors'],
  data() {
    return {
      dialog: false,
      id: false,
    };
  },
  methods: {
    review(id) {
      this.id = id;
      this.dialog = true;
    },
  },
};
</script>
<style lang="scss" scoped>
.shade {
  background: #ffcdd2;
  padding: 16px;
}
</style>
