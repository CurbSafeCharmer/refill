<template>
  <div id="app">
    <v-app>
      <v-toolbar color="primary" dark>
        <v-toolbar-title>{{ msg('appname') }}<sup>&alpha;</sup></v-toolbar-title>
        <v-spacer></v-spacer>

        <v-toolbar-items class="hidden-sm-and-down">
          <v-btn icon href="https://en.wikipedia.org/wiki/Wikipedia:ReFill">
            <v-icon>help</v-icon>
          </v-btn>
          <v-btn icon href="https://en.wikipedia.org/wiki/Wikipedia_talk:ReFill">
            <v-icon>bug_report</v-icon>
          </v-btn>
          <v-btn icon href="https://github.com/curbsafecharmer/refill">
            <v-icon>code</v-icon>
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>

      <v-container fluid fill-height>
        <transition name="slide">
          <router-view></router-view>
        </transition>
      </v-container>
    </v-app>
  </div>
</template>

<script>
export default {
  name: 'app',
  data () {
    return {
    }
  }
}
</script>

<style lang="scss">
.nl {
  color: inherit !important;
  text-decoration: none !important;
}
</style>
