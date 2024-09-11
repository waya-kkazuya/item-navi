<template>
  <p>Count is: {{ count }}</p>
  <button @click="increment">+</button>
</template>

<script setup>
import { ref } from 'vue'

let count = ref(0)

const increment = () => { 
count.value += 1
}
</script>



<!-- <template>
  <div>{{ upperCaseName }}</div>
</template>

<script setup>
import { ref, computed } from 'vue';

let name = ref('John');
let upperCaseName = computed(() => name.value.toUpperCase());
</script>
 -->

<!-- <template>
  <div>Hello {{ msg }}</div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  msg: String
});

defineExpose({
  props
})
</script> -->