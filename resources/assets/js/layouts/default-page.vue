<template>
    <div id="main-wrapper">
        <app-header></app-header>
        <app-sidebar></app-sidebar>

        <div class="page-wrapper">
            <div class="container-fluid">
                <router-view></router-view>
                <app-right-sidebar></app-right-sidebar>
            </div>
        	<app-footer></app-footer>
        </div>
    </div>
</template>


<script>
    import AppHeader from './header.vue'
    import AppSidebar from './sidebar.vue'
    import AppFooter from './footer.vue'
    import AppRightSidebar from './right-sidebar.vue'

    export default {
        data(){
            return {
            }
        },
        methods : {
        },
        components: {
            AppHeader, AppSidebar, AppFooter, AppRightSidebar
        },
        mounted() {
            helper.notification();
            $('body').addClass("fix-header fix-sidebar card-no-border");
            $("body").trigger("resize");
            $(".fix-header .topbar").stick_in_parent();
            $(".scroll-sidebar, .slimScrollDiv").css("overflow-x", "visible").parent().css("overflow", "visible");
            $('#sidebarnav').metisMenu();
            $('.scroll-sidebar').slimScroll({
                position: 'left'
                , size: "5px"
                , height: '100%'
                , color: '#dcdcdc'
            });
            $('.message-scroll').slimScroll({
                position: 'right'
                , size: "5px"
                , height: '570'
                , color: '#dcdcdc'
            });
        },
        watch: {
        }
    }
</script>
