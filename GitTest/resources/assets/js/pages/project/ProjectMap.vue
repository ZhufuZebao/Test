<template>
  <GMaps id="allmap" class="Gmaps"></GMaps>
</template>
<script>
  import GMaps from '../../components/project/ProjectGmaps'
  export default {
    data: function () {
      return {
        maps: {},
      }
    },
    components: {  GMaps },
    methods: {
    },
  }
</script>
<style scoped>

</style>

