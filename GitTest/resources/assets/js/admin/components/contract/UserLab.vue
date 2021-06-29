<template>
  <ul class="person-inf">
    <h3>{{friendInformation.name}}</h3>
    <h3>({{friendInformation.id}})</h3>
  </ul>
</template>
<script>
    import Messages from "../../../mixins/Messages";
    import Calendar from "../../../mixins/Calendar";
    export default {
        name: "UserLab",
        components: {
        },
        mixins: [Messages,Calendar],
        props: {
            friendInformation: Object,
        },
        data() {
            return {
                showConsume:true,
                showContain:false,
                showBrowse:false,
                showHistory:false,
                showRate:false,
            }
        },
        methods: {
            show: function (show) {
                this.$parent.show(show);
            },
        },
        created() {
        },
        watch: {

        },
        mounted() {
        }
    }
</script>
