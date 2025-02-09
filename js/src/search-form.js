/**
 * @param {MouseEvent} e
 * @returns {boolean}
 */
function cancelEvent(e) {
    e.stopPropagation();
    e.preventDefault();
    return false;
}

/**
 * @param {HTMLButtonElement} clearButton
 * @param {HTMLSelectElement[]} selectElements
 */
function bindClearButtonAction(clearButton, selectElements) {
    clearButton.addEventListener('click', e => {
        selectElements.forEach(dropDown => {
            let blankValue = '';

            if (dropDown.id === 'app-state-filter') {
                blankValue = 'USA';
            }

            dropDown.value = blankValue;
        });

        clearButton.closest('form').submit();

        return cancelEvent(e);
    });
}

export function searchForm() {
    const clearButton = document.querySelector('#app-clear-button');
    /**
     * @type {NodeListOf<HTMLInputElement|HTMLSelectElement>}
     */
    const formElements = document.querySelectorAll('#app-search-form input,#app-search-form select');

    if (null !== clearButton) {
        bindClearButtonAction(clearButton, Array.from(formElements).filter(el => 'SELECT' === el.tagName));
    }

    formElements.forEach(el => el.addEventListener('change', () => el.closest('form').submit()));
}