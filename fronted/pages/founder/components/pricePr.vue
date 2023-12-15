<template>
    <div class="table__fixed" :class="mode ? 'table__fixedday' : 'table__fixednight'">
        <div class="table__flexed">
            <el-tag effect="plain">{{ $t("message.charter_capital") }}: {{ charter_capital | formatMoney(2) }}</el-tag>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
    props: ['charter_capital'],
    computed: {
        ...mapGetters({
            mode: "MODE"
        }),
    }
}
</script>

<style>

</style>
