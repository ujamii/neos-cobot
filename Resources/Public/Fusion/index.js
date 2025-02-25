const buttons = document.querySelectorAll('.generate, .save');
buttons.forEach((button) => {
	button.addEventListener('click', () => {
			button.innerHTML = 'Loading...';
			button.classList.add('neos-disabled', 'pointer-events-none');
	});
});

const startEditMode = (element) => {
	const editButton = element.querySelector('button.edit');
	const generateButton = element.querySelector('a.generate');
	const saveButton = element.querySelector('button.save');
	const textarea = element.querySelector('textarea.edit-alt-text');
	const altText = element.querySelector('.alt-text');

	editButton.innerHTML = '<i class="fas fa-times"></i>';
	generateButton.classList.add('hidden');
	saveButton.classList.remove('hidden');
	altText.classList.add('hidden');
	textarea.classList.remove('hidden');

	element.classList.add('edit-mode');
}

const stopEditMode = (element) => {
	const editButton = element.querySelector('button.edit');
	const generateButton = element.querySelector('a.generate');
	const saveButton = element.querySelector('button.save');
	const textarea = element.querySelector('textarea.edit-alt-text');
	const altText = element.querySelector('.alt-text');

	editButton.innerHTML = '<i class="fas fa-pencil-alt"></i>';
	saveButton.classList.add('hidden');
	generateButton.classList.remove('hidden');
	altText.classList.remove('hidden');
	textarea.classList.add('hidden');
	textarea.value = textarea.dataset.previousText;

	element.classList.remove('edit-mode');
}

document.querySelectorAll('.asset-list-item').forEach((element) => {
		const editButton = element.querySelector('button.edit');
		editButton.addEventListener('click', () => {
			if(element.classList.contains('edit-mode')) {
				stopEditMode(element);
			} else {
				startEditMode(element);
			}
		});
});
