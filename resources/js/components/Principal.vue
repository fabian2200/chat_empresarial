<template>
    <div>
        <div class="app-container d-flex">
            <div class="menu-container">
                <div class="menu-item d-flex flex-column">
                    <router-link class="menu-item-link" :class="{ 'active': activeMenu === 'chat' }" @click="setActiveMenu('chat')" to="/principal/chat"><i class="fas fa-comments"></i> Chats</router-link>
                    <router-link class="menu-item-link" :class="{ 'active': activeMenu === 'grupos' }" @click="setActiveMenu('grupos')" to="/principal/grupos"><i class="fas fa-users"></i> Grupos</router-link>
                </div>
            </div>
            <router-view></router-view>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            activeMenu: 'chat'
        }
    },
    mounted() {
        this.verificarRuta();
    },
    methods: {
        setActiveMenu(menu) {
            this.activeMenu = menu;
        },
        verificarRuta() {
            if(this.$route.path === '/principal/chat') {
                this.activeMenu = 'chat';
            }else if(this.$route.path === '/principal/grupos') {
                this.activeMenu = 'grupos';
            }
        }
    }
}
</script>
<style>
.app-container {
    padding: 20px;
}

.app-container::after {
    position: fixed;
    top: 0px;
    left: 0px;
    width: 100%;
    height: 127px;
    content: "";
    background-color: #061224;
    z-index: -100;
}

.app-container::before {
    position: fixed;
    top: 127px;
    left: 0px;
    width: 100%;
    height: calc(100vh - 127px);
    content: "";
    background-color: #dfdfdf;
    z-index: -99;
}

.menu-container {
    width: 88px;
    height: calc(100vh - 40px);
    background-color: #919294;
    color: #fff;
}

.menu-item-link {
    cursor: pointer;
    transition: all 0.3s ease;
    color: #fff;
    padding: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    margin: 10px;
    width: 60px;
    height: 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 12px;
}

.menu-item-link:hover {
    background-color: #b9b9b9;
}

.menu-item-link.active {
    background-color: #fff;
    color: #000;
}

</style>