<template>
  <div id="app">
    <v-app>
      <v-app-bar color="primary" dark>
        <div class="v-toolbar__title"><a :href="$config.publicPath">{{ msg('appname') }}</a></div>
        <v-spacer></v-spacer>

        <div class="v-toolbar-items hidden-sm-and-down">
          <v-btn icon href="https://en.wikipedia.org/wiki/Wikipedia:ReFill">
            <span class="material-icons">help</span>
          </v-btn>
          <v-btn icon href="https://en.wikipedia.org/wiki/Wikipedia_talk:ReFill">
            <span class="material-icons">bug_report</span>
          </v-btn>
          <v-btn icon href="https://github.com/curbsafecharmer/refill">
            <span class="material-icons">code</span>
          </v-btn>
        </div>
      </v-app-bar>

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
.v-toolbar__title a {
  color: white !important;
  text-decoration: none !important;
}
</style>
