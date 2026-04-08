document.addEventListener('DOMContentLoaded', function () {
	const questions = document.querySelectorAll('.nsk-faq-question');

	questions.forEach(question => {
		question.addEventListener('click', () => {
			const item = question.parentElement;
			const answer = item.querySelector('.nsk-faq-answer');
			const isOpen = item.classList.contains('active');

			item.classList.toggle('active');
			question.setAttribute('aria-expanded', String(!isOpen));

			if (answer) {
				if (isOpen) {
					answer.setAttribute('hidden', '');
				} else {
					answer.removeAttribute('hidden');
				}
			}
		});
	});
});
