// resources/js/plugins/vuetify.js
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

// import the MDI iconset helpers
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg'

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          surface: '#FFFFFF',
          'surface-variant': '#E7E0EC',
          // add any custom colors if you want
        },
      },
    },
  },
  // this auto-injects the CSS font and sets defaultSet to MDI
  defaultAssets: {
    icons: 'mdi',
  },
  icons: {
    defaultSet: 'mdi',
    // no custom sets needed when using the font
  },
})