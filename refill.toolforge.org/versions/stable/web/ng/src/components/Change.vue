<template>
  <v-row class="flex-wrap">
    <v-col cols="5">
      <span class="wikicode">{{ change.old }}</span>
    </v-col>
    <v-col cols="2">
      <span class="arrow">➡</span>
    </v-col>
    <v-col cols="5">
      <span class="wikicode">{{ change.new }}</span>
    </v-col>
  </v-row>
</template>
<script>
export default {
  props: ['change'],
};
</script>
<style lang="scss">
.wikicode {
  font-family: monospace;
  overflow-wrap: break-word;
}
.arrow {
  color: #bbb;
  font-size: 40px;
}
</style>
