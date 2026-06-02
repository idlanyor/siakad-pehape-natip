# Component Library Documentation

Dokumentasi lengkap untuk reusable Vue 3 components yang tersedia di Ujian Service.

## Table of Contents

- [UI Components](#ui-components)
  - [Button](#button)
  - [Modal](#modal)
  - [Card](#card)
  - [Alert](#alert)
  - [Badge](#badge)
  - [Loading](#loading)
  - [Dropdown](#dropdown)
  - [Tabs & TabPane](#tabs--tabpane)
  - [DataTable](#datatable)
  - [Toast](#toast)
- [Form Components](#form-components)
  - [Input](#input)
  - [Textarea](#textarea)
  - [Select](#select)
  - [Checkbox](#checkbox)
  - [Radio](#radio)
  - [DatePicker](#datepicker)
- [Composables](#composables)
  - [useToast](#usetoast)
  - [useForm](#useform)
  - [useModal](#usemodal)

---

## UI Components

### Button

Komponen button dengan berbagai variant, size, dan state.

**Import:**
```javascript
import { Button } from '@/Components';
```

**Props:**
- `variant` (String): 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link' (default: 'primary')
- `size` (String): 'sm', 'md', 'lg' (default: 'md')
- `outline` (Boolean): Outline style (default: false)
- `block` (Boolean): Full width button (default: false)
- `disabled` (Boolean): Disabled state (default: false)
- `loading` (Boolean): Loading state dengan spinner (default: false)
- `icon` (String): Font Awesome icon class
- `href` (String): Jika diisi, render sebagai Inertia Link
- `type` (String): Button type (default: 'button')

**Events:**
- `@click`: Emitted saat button diklik

**Usage:**
```vue
<template>
  <Button variant="primary" @click="handleClick">
    Click Me
  </Button>
  
  <Button variant="success" icon="fas fa-save" :loading="saving">
    Save
  </Button>
  
  <Button variant="danger" outline size="sm">
    Delete
  </Button>
  
  <Button href="/dashboard" variant="link">
    Go to Dashboard
  </Button>
</template>
```

---

### Modal

Komponen modal dialog dengan backdrop dan berbagai konfigurasi.

**Import:**
```javascript
import { Modal } from '@/Components';
```

**Props:**
- `show` (Boolean): Control modal visibility (v-model)
- `title` (String): Modal title
- `size` (String): 'sm', 'md', 'lg', 'xl' (default: 'md')
- `centered` (Boolean): Center modal vertically (default: false)
- `scrollable` (Boolean): Scrollable body (default: false)
- `closable` (Boolean): Show close button (default: true)
- `closeOnBackdrop` (Boolean): Close on backdrop click (default: true)
- `closeOnEscape` (Boolean): Close on ESC key (default: true)
- `showFooter` (Boolean): Show footer (default: true)
- `showConfirm` (Boolean): Show confirm button (default: true)
- `confirmText` (String): Confirm button text (default: 'Confirm')
- `cancelText` (String): Cancel button text (default: 'Cancel')
- `confirmVariant` (String): Confirm button variant (default: 'primary')
- `confirmDisabled` (Boolean): Disable confirm button (default: false)
- `confirmLoading` (Boolean): Loading state for confirm button (default: false)

**Events:**
- `@update:show`: Emitted when modal visibility changes
- `@close`: Emitted when modal is closed
- `@confirm`: Emitted when confirm button is clicked

**Slots:**
- `header`: Custom header content
- `default`: Modal body content
- `footer`: Custom footer content

**Usage:**
```vue
<template>
  <Button @click="showModal = true">Open Modal</Button>
  
  <Modal
    v-model:show="showModal"
    title="Confirm Action"
    size="md"
    @confirm="handleConfirm"
  >
    Are you sure you want to proceed?
  </Modal>
  
  <!-- Custom Footer -->
  <Modal v-model:show="customModal" title="Custom Modal">
    <p>Modal content here</p>
    
    <template #footer>
      <Button variant="secondary" @click="customModal = false">
        Cancel
      </Button>
      <Button variant="primary" @click="handleSave">
        Save Changes
      </Button>
    </template>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import { Modal, Button } from '@/Components';

const showModal = ref(false);
const customModal = ref(false);

const handleConfirm = () => {
  // Handle confirmation
  showModal.value = false;
};
</script>
```

---

### Card

Komponen card dengan header, body, dan footer.

**Import:**
```javascript
import { Card } from '@/Components';
```

**Props:**
- `title` (String): Card title
- `variant` (String): 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'
- `outline` (Boolean): Outline style (default: false)
- `collapsed` (Boolean): Collapsed state (default: false)
- `loading` (Boolean): Show loading overlay (default: false)
- `noPadding` (Boolean): Remove body padding (default: false)
- `headerBorder` (Boolean): Show header border (default: true)
- `footerBorder` (Boolean): Show footer border (default: true)

**Slots:**
- `header`: Custom header content
- `tools`: Card tools (top right)
- `default`: Card body content
- `footer`: Card footer content

**Usage:**
```vue
<template>
  <Card title="User Statistics" variant="primary">
    <p>Card content here</p>
  </Card>
  
  <Card title="Data Table" :loading="loading">
    <template #tools>
      <Button size="sm" variant="primary">
        <i class="fas fa-plus"></i>
      </Button>
    </template>
    
    <DataTable :data="users" :columns="columns" />
    
    <template #footer>
      <Button variant="primary">Save</Button>
    </template>
  </Card>
</template>
```

---

### Alert

Komponen alert dengan berbagai variant dan auto-dismiss.

**Import:**
```javascript
import { Alert } from '@/Components';
```

**Props:**
- `variant` (String): 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark' (default: 'info')
- `title` (String): Alert title
- `dismissible` (Boolean): Show close button (default: false)
- `icon` (String|Boolean): Icon class atau true untuk default icon
- `autoDismiss` (Number|Boolean): Auto dismiss after milliseconds (default: false)
- `modelValue` (Boolean): Control visibility (v-model)

**Events:**
- `@update:modelValue`: Emitted when visibility changes
- `@close`: Emitted when alert is closed

**Usage:**
```vue
<template>
  <Alert variant="success" icon dismissible>
    Operation completed successfully!
  </Alert>
  
  <Alert variant="danger" title="Error" :auto-dismiss="5000">
    Something went wrong. Please try again.
  </Alert>
  
  <Alert v-model="showAlert" variant="warning" dismissible>
    This is a warning message.
  </Alert>
</template>
```

---

### Badge

Komponen badge untuk labels dan counts.

**Import:**
```javascript
import { Badge } from '@/Components';
```

**Props:**
- `variant` (String): 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark' (default: 'primary')
- `pill` (Boolean): Pill style (default: false)
- `icon` (String): Font Awesome icon class

**Usage:**
```vue
<template>
  <Badge variant="success">Active</Badge>
  <Badge variant="danger" pill>5</Badge>
  <Badge variant="primary" icon="fas fa-user">Admin</Badge>
</template>
```

---

### Loading

Komponen loading spinner dengan berbagai konfigurasi.

**Import:**
```javascript
import { Loading } from '@/Components';
```

**Props:**
- `show` (Boolean): Show loading (default: true)
- `size` (String): 'sm', 'md', 'lg' (default: 'md')
- `variant` (String): Color variant (default: 'primary')
- `type` (String): 'border', 'grow' (default: 'border')
- `text` (String): Loading text (default: 'Loading...')
- `showText` (Boolean): Show text below spinner (default: false)
- `centered` (Boolean): Center in container (default: false)
- `overlay` (Boolean): Full overlay (default: false)

**Usage:**
```vue
<template>
  <Loading :show="loading" centered />
  
  <Loading 
    :show="processing" 
    size="lg" 
    text="Processing..." 
    show-text 
    overlay 
  />
</template>
```

---

### Dropdown

Komponen dropdown menu.

**Import:**
```javascript
import { Dropdown } from '@/Components';
```

**Props:**
- `triggerText` (String): Button text (default: 'Dropdown')
- `variant` (String): Button variant (default: 'secondary')
- `size` (String): Button size (default: 'md')
- `align` (String): 'left', 'right' (default: 'left')
- `split` (Boolean): Split button style (default: false)

**Events:**
- `@toggle`: Emitted when dropdown toggles
- `@open`: Emitted when dropdown opens
- `@close`: Emitted when dropdown closes

**Slots:**
- `trigger`: Custom trigger button
- `default`: Dropdown menu items

**Usage:**
```vue
<template>
  <Dropdown trigger-text="Actions" variant="primary">
    <a class="dropdown-item" href="#" @click.prevent="handleEdit">
      <i class="fas fa-edit mr-2"></i> Edit
    </a>
    <a class="dropdown-item" href="#" @click.prevent="handleDelete">
      <i class="fas fa-trash mr-2"></i> Delete
    </a>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="#">
      <i class="fas fa-cog mr-2"></i> Settings
    </a>
  </Dropdown>
</template>
```

---

### Tabs & TabPane

Komponen tabs untuk navigasi konten.

**Import:**
```javascript
import { Tabs, TabPane } from '@/Components';
```

**Tabs Props:**
- `modelValue` (Number): Active tab index (v-model)
- `pills` (Boolean): Pills style (default: false)
- `vertical` (Boolean): Vertical tabs (default: false)
- `justified` (Boolean): Justified tabs (default: false)
- `fill` (Boolean): Fill tabs (default: false)

**TabPane Props:**
- `title` (String): Tab title (required)
- `icon` (String): Tab icon
- `badge` (String|Number): Tab badge

**Events:**
- `@update:modelValue`: Emitted when active tab changes
- `@change`: Emitted when tab changes

**Usage:**
```vue
<template>
  <Tabs v-model="activeTab">
    <TabPane title="Profile" icon="fas fa-user">
      <p>Profile content</p>
    </TabPane>
    
    <TabPane title="Settings" icon="fas fa-cog">
      <p>Settings content</p>
    </TabPane>
    
    <TabPane title="Messages" icon="fas fa-envelope" badge="5">
      <p>Messages content</p>
    </TabPane>
  </Tabs>
</template>

<script setup>
import { ref } from 'vue';
import { Tabs, TabPane } from '@/Components';

const activeTab = ref(0);
</script>
```

---

### DataTable

Komponen tabel dengan sorting, filtering, dan pagination.

**Import:**
```javascript
import { DataTable } from '@/Components';
```

**Props:**
- `data` (Array): Table data (required)
- `columns` (Array): Column definitions (required)
- `perPage` (Number): Items per page (default: 10)
- `loading` (Boolean): Loading state (default: false)
- `striped` (Boolean): Striped rows (default: true)
- `bordered` (Boolean): Bordered table (default: false)
- `hover` (Boolean): Hover effect (default: true)
- `small` (Boolean): Small table (default: false)
- `selectable` (Boolean): Enable row selection (default: false)
- `showHeader` (Boolean): Show header (default: true)
- `showFooter` (Boolean): Show footer (default: true)
- `showSearch` (Boolean): Show search (default: true)
- `searchPlaceholder` (String): Search placeholder (default: 'Search...')
- `emptyText` (String): Empty state text (default: 'No data available')
- `rowKey` (String): Unique row key (default: 'id')

**Column Definition:**
```javascript
{
  key: 'name',           // Data key (required)
  label: 'Name',         // Column label (required)
  sortable: true,        // Enable sorting (optional)
  width: '200px',        // Column width (optional)
  formatter: (value, row) => {  // Value formatter (optional)
    return value.toUpperCase();
  }
}
```

**Events:**
- `@sort`: Emitted when sorting changes
- `@search`: Emitted when search query changes
- `@select`: Emitted when row selection changes
- `@page-change`: Emitted when page changes

**Slots:**
- `actions`: Header actions
- `cell-{key}`: Custom cell content
- `actions`: Row actions

**Usage:**
```vue
<template>
  <DataTable
    :data="users"
    :columns="columns"
    :loading="loading"
    selectable
    @select="handleSelect"
  >
    <template #actions>
      <Button variant="primary" size="sm" @click="handleAdd">
        <i class="fas fa-plus mr-2"></i> Add User
      </Button>
    </template>
    
    <template #cell-status="{ value }">
      <Badge :variant="value === 'active' ? 'success' : 'danger'">
        {{ value }}
      </Badge>
    </template>
    
    <template #actions="{ row }">
      <Button variant="info" size="sm" @click="handleEdit(row)">
        <i class="fas fa-edit"></i>
      </Button>
      <Button variant="danger" size="sm" @click="handleDelete(row)">
        <i class="fas fa-trash"></i>
      </Button>
    </template>
  </DataTable>
</template>

<script setup>
import { ref } from 'vue';
import { DataTable, Button, Badge } from '@/Components';

const users = ref([
  { id: 1, name: 'John Doe', email: 'john@example.com', status: 'active' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', status: 'inactive' }
]);

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'status', label: 'Status', sortable: true }
];

const loading = ref(false);
</script>
```

---

### Toast

Komponen toast notification (digunakan dengan useToast composable).

**Import:**
```javascript
import { Toast } from '@/Components';
```

**Usage:**
```vue
<template>
  <Toast />
</template>
```

Lihat [useToast](#usetoast) untuk cara penggunaan.

---

## Form Components

### Input

Komponen input field dengan validation.

**Import:**
```javascript
import { Input } from '@/Components';
```

**Props:**
- `modelValue` (String|Number): Input value (v-model)
- `type` (String): Input type (default: 'text')
- `label` (String): Input label
- `placeholder` (String): Placeholder text
- `help` (String): Help text
- `error` (String): Error message
- `disabled` (Boolean): Disabled state (default: false)
- `readonly` (Boolean): Readonly state (default: false)
- `required` (Boolean): Required field (default: false)
- `size` (String): 'sm', 'md', 'lg' (default: 'md')
- `prepend` (String): Prepend text
- `append` (String): Append text
- `min` (String|Number): Min value (for number type)
- `max` (String|Number): Max value (for number type)
- `step` (String|Number): Step value (for number type)
- `maxlength` (String|Number): Max length

**Events:**
- `@update:modelValue`: Emitted when value changes
- `@blur`: Emitted on blur
- `@focus`: Emitted on focus

**Slots:**
- `prepend`: Custom prepend content
- `append`: Custom append content

**Usage:**
```vue
<template>
  <Input
    v-model="form.name"
    label="Name"
    placeholder="Enter your name"
    :error="form.errors.name"
    required
  />
  
  <Input
    v-model="form.price"
    type="number"
    label="Price"
    prepend="Rp"
    :min="0"
    :step="1000"
  />
  
  <Input
    v-model="form.email"
    type="email"
    label="Email"
    help="We'll never share your email"
  />
</template>
```

---

### Textarea

Komponen textarea dengan character count.

**Import:**
```javascript
import { Textarea } from '@/Components';
```

**Props:**
- `modelValue` (String): Textarea value (v-model)
- `label` (String): Textarea label
- `placeholder` (String): Placeholder text
- `help` (String): Help text
- `error` (String): Error message
- `disabled` (Boolean): Disabled state (default: false)
- `readonly` (Boolean): Readonly state (default: false)
- `required` (Boolean): Required field (default: false)
- `rows` (String|Number): Number of rows (default: 3)
- `maxlength` (String|Number): Max length
- `showCharCount` (Boolean): Show character count (default: false)

**Events:**
- `@update:modelValue`: Emitted when value changes
- `@blur`: Emitted on blur
- `@focus`: Emitted on focus

**Usage:**
```vue
<template>
  <Textarea
    v-model="form.description"
    label="Description"
    placeholder="Enter description"
    :rows="5"
    :maxlength="500"
    show-char-count
    :error="form.errors.description"
  />
</template>
```

---

### Select

Komponen select dropdown dengan multiple support.

**Import:**
```javascript
import { Select } from '@/Components';
```

**Props:**
- `modelValue` (String|Number|Array): Selected value (v-model)
- `options` (Array): Options array (required)
- `label` (String): Select label
- `placeholder` (String): Placeholder text (default: 'Select an option')
- `help` (String): Help text
- `error` (String): Error message
- `disabled` (Boolean): Disabled state (default: false)
- `required` (Boolean): Required field (default: false)
- `multiple` (Boolean): Multiple selection (default: false)
- `size` (String): 'sm', 'md', 'lg' (default: 'md')

**Options Format:**
```javascript
// Simple array
['Option 1', 'Option 2', 'Option 3']

// Object array
[
  { value: 1, label: 'Option 1' },
  { value: 2, label: 'Option 2', disabled: true },
  { value: 3, label: 'Option 3' }
]
```

**Events:**
- `@update:modelValue`: Emitted when value changes
- `@change`: Emitted when selection changes
- `@blur`: Emitted on blur
- `@focus`: Emitted on focus

**Usage:**
```vue
<template>
  <Select
    v-model="form.category"
    :options="categories"
    label="Category"
    placeholder="Select category"
    :error="form.errors.category"
    required
  />
  
  <Select
    v-model="form.tags"
    :options="tags"
    label="Tags"
    multiple
  />
</template>

<script setup>
const categories = [
  { value: 1, label: 'Technology' },
  { value: 2, label: 'Science' },
  { value: 3, label: 'Arts' }
];

const tags = ['Vue', 'Laravel', 'JavaScript', 'PHP'];
</script>
```

---

### Checkbox

Komponen checkbox dengan array support.

**Import:**
```javascript
import { Checkbox } from '@/Components';
```

**Props:**
- `modelValue` (Boolean|Array): Checked value (v-model)
- `value` (String|Number|Boolean): Checkbox value (default: true)
- `label` (String): Checkbox label
- `help` (String): Help text
- `error` (String): Error message
- `disabled` (Boolean): Disabled state (default: false)
- `required` (Boolean): Required field (default: false)
- `inline` (Boolean): Inline display (default: false)

**Events:**
- `@update:modelValue`: Emitted when value changes
- `@change`: Emitted when checked state changes

**Usage:**
```vue
<template>
  <!-- Single checkbox -->
  <Checkbox v-model="form.agree" label="I agree to terms" />
  
  <!-- Multiple checkboxes -->
  <Checkbox v-model="form.permissions" value="read" label="Read" inline />
  <Checkbox v-model="form.permissions" value="write" label="Write" inline />
  <Checkbox v-model="form.permissions" value="delete" label="Delete" inline />
</template>

<script setup>
import { reactive } from 'vue';

const form = reactive({
  agree: false,
  permissions: []
});
</script>
```

---

### Radio

Komponen radio button.

**Import:**
```javascript
import { Radio } from '@/Components';
```

**Props:**
- `modelValue` (String|Number|Boolean): Selected value (v-model)
- `value` (String|Number|Boolean): Radio value (required)
- `name` (String): Radio group name (required)
- `label` (String): Radio label
- `help` (String): Help text
- `error` (String): Error message
- `disabled` (Boolean): Disabled state (default: false)
- `required` (Boolean): Required field (default: false)
- `inline` (Boolean): Inline display (default: false)

**Events:**
- `@update:modelValue`: Emitted when value changes
- `@change`: Emitted when selection changes

**Usage:**
```vue
<template>
  <Radio v-model="form.gender" name="gender" value="male" label="Male" inline />
  <Radio v-model="form.gender" name="gender" value="female" label="Female" inline />
  <Radio v-model="form.gender" name="gender" value="other" label="Other" inline />
</template>

<script setup>
import { reactive } from 'vue';

const form = reactive({
  gender: 'male'
});
</script>
```

---

### DatePicker

Komponen date picker.

**Import:**
```javascript
import { DatePicker } from '@/Components';
```

**Props:**
- `modelValue` (String): Date value (v-model)
- `label` (String): DatePicker label
- `placeholder` (String): Placeholder text
- `help` (String): Help text
- `error` (String): Error message
- `disabled` (Boolean): Disabled state (default: false)
- `readonly` (Boolean): Readonly state (default: false)
- `required` (Boolean): Required field (default: false)
- `showIcon` (Boolean): Show calendar icon (default: true)
- `min` (String): Min date (YYYY-MM-DD)
- `max` (String): Max date (YYYY-MM-DD)

**Events:**
- `@update:modelValue`: Emitted when value changes
- `@blur`: Emitted on blur
- `@focus`: Emitted on focus

**Usage:**
```vue
<template>
  <DatePicker
    v-model="form.birthdate"
    label="Birth Date"
    :max="today"
    :error="form.errors.birthdate"
    required
  />
  
  <DatePicker
    v-model="form.startDate"
    label="Start Date"
    :min="today"
  />
</template>

<script setup>
import { reactive } from 'vue';

const today = new Date().toISOString().split('T')[0];

const form = reactive({
  birthdate: '',
  startDate: ''
});
</script>
```

---

## Composables

### useToast

Composable untuk menampilkan toast notifications.

**Import:**
```javascript
import { useToast } from '@/Components';
```

**Methods:**
- `show(message, options)`: Show toast
- `success(message, options)`: Show success toast
- `error(message, options)`: Show error toast
- `warning(message, options)`: Show warning toast
- `info(message, options)`: Show info toast
- `remove(id)`: Remove specific toast
- `clear()`: Clear all toasts

**Options:**
- `variant` (String): Toast variant
- `duration` (Number): Auto-dismiss duration in ms (default: 3000)
- `dismissible` (Boolean): Show close button (default: true)
- `icon` (String): Custom icon
- `title` (String): Toast title

**Usage:**
```vue
<script setup>
import { useToast } from '@/Components';

const toast = useToast();

const handleSave = async () => {
  try {
    await saveData();
    toast.success('Data saved successfully!');
  } catch (error) {
    toast.error('Failed to save data', {
      duration: 5000
    });
  }
};

const handleWarning = () => {
  toast.warning('This action cannot be undone', {
    title: 'Warning',
    duration: 0 // No auto-dismiss
  });
};
</script>
```

**Setup in AppLayout:**
```vue
<template>
  <div>
    <!-- Your layout content -->
    <Toast />
  </div>
</template>

<script setup>
import { Toast } from '@/Components';
</script>
```

---

### useForm

Composable untuk form handling dengan Inertia.js.

**Import:**
```javascript
import { useForm } from '@/Components';
```

**Properties:**
- `data`: Form data object
- `errors`: Validation errors object
- `processing`: Processing state
- `wasSuccessful`: Success state
- `recentlySuccessful`: Recently successful state
- `hasErrors`: Computed property for error check

**Methods:**
- `reset(...fields)`: Reset form fields
- `clearErrors(...fields)`: Clear validation errors
- `setError(field, message)`: Set error for field
- `setErrors(errors)`: Set multiple errors
- `get(url, options)`: Submit GET request
- `post(url, options)`: Submit POST request
- `put(url, options)`: Submit PUT request
- `patch(url, options)`: Submit PATCH request
- `delete(url, options)`: Submit DELETE request

**Usage:**
```vue
<template>
  <form @submit.prevent="handleSubmit">
    <Input
      v-model="form.data.name"
      label="Name"
      :error="form.errors.name"
      required
    />
    
    <Input
      v-model="form.data.email"
      type="email"
      label="Email"
      :error="form.errors.email"
      required
    />
    
    <Button
      type="submit"
      variant="primary"
      :loading="form.processing"
      :disabled="form.processing"
    >
      Save
    </Button>
    
    <Alert v-if="form.recentlySuccessful" variant="success">
      Saved successfully!
    </Alert>
  </form>
</template>

<script setup>
import { useForm, Input, Button, Alert } from '@/Components';

const form = useForm({
  name: '',
  email: ''
});

const handleSubmit = () => {
  form.post('/api/users', {
    onSuccess: () => {
      form.reset();
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
    }
  });
};
</script>
```

---

### useModal

Composable untuk modal management.

**Import:**
```javascript
import { useModal } from '@/Components';
```

**Methods:**
- `register(id)`: Register modal
- `show(id)`: Show modal
- `hide(id)`: Hide modal
- `toggle(id)`: Toggle modal
- `isOpen(id)`: Check if modal is open

**Usage:**
```vue
<template>
  <Button @click="modal.show('confirmDelete')">
    Delete
  </Button>
  
  <Modal
    :show="modal.isOpen('confirmDelete')"
    @update:show="(value) => value ? modal.show('confirmDelete') : modal.hide('confirmDelete')"
    title="Confirm Delete"
    @confirm="handleDelete"
  >
    Are you sure you want to delete this item?
  </Modal>
</template>

<script setup>
import { useModal, Modal, Button } from '@/Components';

const modal = useModal();
modal.register('confirmDelete');

const handleDelete = () => {
  // Delete logic
  modal.hide('confirmDelete');
};
</script>
```

---

## Best Practices

### 1. Import Components

Gunakan named imports untuk better tree-shaking:

```javascript
import { Button, Modal, Input } from '@/Components';
```

### 2. Form Validation

Gunakan `useForm` composable untuk handling form dengan validation:

```javascript
const form = useForm({
  name: '',
  email: ''
});

// Errors akan otomatis di-handle dari response Laravel
```

### 3. Toast Notifications

Setup Toast component di AppLayout untuk global access:

```vue
<!-- AppLayout.vue -->
<template>
  <div>
    <!-- Layout content -->
    <Toast />
  </div>
</template>
```

### 4. Modal Management

Gunakan `useModal` untuk manage multiple modals:

```javascript
const modal = useModal();
modal.register('create');
modal.register('edit');
modal.register('delete');
```

### 5. DataTable Custom Cells

Gunakan scoped slots untuk custom cell rendering:

```vue
<template #cell-status="{ value, row }">
  <Badge :variant="getStatusVariant(value)">
    {{ value }}
  </Badge>
</template>
```

### 6. Loading States

Selalu tampilkan loading state untuk better UX:

```vue
<Button :loading="processing" :disabled="processing">
  Save
</Button>
```

---

## Examples

Lihat folder `resources/js/Pages` untuk contoh penggunaan lengkap dari semua components.
