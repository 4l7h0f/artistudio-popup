<template>
	<div>
		<h2>{{ isEditMode ? "Edit Popup" : "Create Popup" }}</h2>
		<form @submit.prevent="submitForm">
			<div>
				<label for="title">Title:</label>
				<input
					type="text"
					id="title"
					v-model="formData.title"
					required
				/>
			</div>
			<div>
				<label for="description">Description:</label>
				<!-- WordPress editor will be initialized here -->
				<div id="wp-editor-container" class="wp-editor-container">
					<textarea
						id="popup-editor"
						v-model="formData.description"
					></textarea>
				</div>
				<!-- <textarea
					id="description"
					v-model="formData.description"
					required
				></textarea> -->
			</div>
			<div>
				<label for="page">Page:</label>
				<select id="page" v-model="formData.page" required>
					<option
						v-for="page in pages"
						:key="page.id"
						:value="page.id"
					>
						{{ page.title }}
					</option>
				</select>
			</div>
			<div class="status-container">
				<label>Post Status:</label>
				<label class="switch">
					<input
						type="checkbox"
						v-model="formData.status"
						true-value="publish"
						false-value="draft"
					/>
					<span class="slider"></span>
				</label>
				<span>{{
					formData.status === "publish" ? "Publish" : "Draft"
				}}</span>
			</div>
			<button type="submit">
				{{ isEditMode ? "Update" : "Create" }}
			</button>
			<button type="button" @click="cancelForm">Cancel</button>
		</form>
	</div>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from "vue";
import { useRoute } from "vue-router";
import usePopupForm from "../composables/usePopupForm";

const {
	pages,
	formData,
	isEditMode,
	fetchPages,
	fetchPopup,
	submitForm,
	cancelForm,
} = usePopupForm();

const route = useRoute();
let editorInitialized = false;

const initializeEditor = () => {
  if (typeof wp !== 'undefined' && wp.editor && !editorInitialized) {
    const editorSettings = {
      tinymce: {
        wpautop: true,
        plugins: 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wptextpattern',
        toolbar1: 'formatselect,bold,italic,strikethrough,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,wp_adv,fullscreen',
        toolbar2: 'underline,alignjustify,forecolor,backcolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help,media',
        setup: function(editor) {
          editor.on('change', function() {
            formData.value.description = editor.getContent();
          });
        }
      },
      quicktags: true,
      mediaButtons: true
    };

    wp.editor.initialize('popup-editor', editorSettings);
    editorInitialized = true;
  }
};

const destroyEditor = () => {
  if (typeof wp !== 'undefined' && wp.editor && editorInitialized) {
    wp.editor.remove('popup-editor');
    editorInitialized = false;
  }
};

onMounted(async () => {
  await fetchPages();
  await fetchPopup(route.params.id);

  // Check if WordPress editor is already loaded
  if (typeof wp !== 'undefined' && wp.editor) {
    initializeEditor();
  } else {
    // If not, wait for it to load
    const checkEditor = setInterval(() => {
      if (typeof wp !== 'undefined' && wp.editor) {
        clearInterval(checkEditor);
        initializeEditor();
      }
    }, 100);
  }
});

onBeforeUnmount(() => {
  destroyEditor();
});
</script>

<style scoped>
form div {
	margin-bottom: 15px;
}

label {
	display: block;
	margin-bottom: 5px;
}

input,
textarea,
select {
	width: 100%;
	padding: 8px;
	box-sizing: border-box;
}

button {
	margin-right: 10px;
}

/* Toggle switch styles */
.status-container {
	display: flex;
	align-items: center;
	gap: 10px;
}

.switch {
	position: relative;
	display: inline-block;
	width: 34px;
	height: 20px;
}

.switch input {
	opacity: 0;
	width: 0;
	height: 0;
}

.slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: 0.4s;
	border-radius: 20px;
}

.slider:before {
	position: absolute;
	content: "";
	height: 14px;
	width: 14px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: 0.4s;
	border-radius: 50%;
}

input:checked + .slider {
	background-color: #2196f3;
}

input:checked + .slider:before {
	transform: translateX(14px);
}

.wp-editor-container {
	margin-top: 10px;
	width: 100%;
}
#popup-editor {
	width: 100%;
	min-height: 200px;
}
/* Add Thickbox CSS for media modal */
#wp-editor-container .mce-widget.mce-notification.mce-notification-warning {
	display: none !important;
}

button {
	margin-top: 10px;
	padding: 10px 20px;
	background: #0073aa;
	color: white;
	border: none;
	border-radius: 4px;
	cursor: pointer;
}

button:hover {
	background: #005177;
}
</style>
