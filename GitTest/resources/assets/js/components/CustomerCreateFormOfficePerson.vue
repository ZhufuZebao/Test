<template>
  <div>
          <div>------------------------------------------------------------------------</div>
          <div class="form-group">
            <label for="people-name">{{ peopleCols.find(key => key.col === 'name').name }}</label>
            <input type="text" class="form-control" id="people-name" v-model="$props.person.name">
          </div>
          <div class="form-group">
            <label for="people-position">{{ peopleCols.find(key => key.col === 'position').name }}</label>
            <input type="text" class="form-control" id="people-position" v-model="$props.person.position">
          </div>
          <div class="form-group">
            <label for="people-dept">{{ peopleCols.find(key => key.col === 'dept').name }}</label>
            <input type="text" class="form-control" id="people-dept" v-model="$props.person.dept">
          </div>
          <div class="form-group">
            <label for="people-role">{{ peopleCols.find(key => key.col === 'role').name }}</label>
            <input type="text" class="form-control" id="people-role" v-model="$props.person.role">
          </div>
          <div class="form-group">
            <label for="people-email">{{ peopleCols.find(key => key.col === 'email').name }}</label>
            <input type="text" class="form-control" id="people-email" v-model="$props.person.email">
          </div>
          <button type="button" class="btn btn-delPerson" @click.prevent="$emit('delPerson')">担当者削除</button>
  </div>
</template>

<script>
import CustomerCols from '../mixins/CustomerCols'
export default {
  mixins: [ CustomerCols ],  //項目名定数
  props:['person'],
  data: function() {
    return {
      billing_equal: false,
    };
  },
};
</script>