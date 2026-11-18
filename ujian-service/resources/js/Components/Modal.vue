<template>
  <UIModal v-bind="$attrs">
    <template v-for="(_, slot) in $slots" #[slot]="scope">
      <slot :name="slot" v-bind="scope || {}" />
    </template>
  </UIModal>
</template>

<script setup>
import UIModal from './UI/Modal.vue';
</script>
