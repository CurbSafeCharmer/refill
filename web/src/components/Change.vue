<template>
  <v-layout row wrap>
    <v-flex xs5>
      <span class="wikicode">{{ change.old }}</span>
    </v-flex>
    <v-flex xs2>
      <span class="arrow">➡</span>
    </v-flex>
    <v-flex xs5>
      <span class="wikicode">{{ change.new }}</span>
    </v-flex>
  </v-layout>
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
