---
paths:
  - 'resources/js/**/*.vue'
---

# Js

## Date fields use the shadcn-vue calendar
Every date field must use the shadcn-vue `Calendar`, via `@/components/ui/date-picker/DatePicker.vue` (v-model is a `YYYY-MM-DD` string or null). Never use a native `<input type="date">`. Inside an Inertia `<Form>`, pair it with a hidden input carrying the field name, e.g. `<input type="hidden" name="due_date" :value="date ?? ''" />`.
