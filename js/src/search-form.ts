function cancelEvent(e: MouseEvent): boolean {
  e.stopPropagation();
  e.preventDefault();
  return false;
}

function bindClearButtonAction(
  clearButton: HTMLButtonElement,
  selectElements: HTMLSelectElement[],
) {
  clearButton.addEventListener("click", (e) => {
    selectElements.forEach((dropDown) => {
      let blankValue = "";

      if (dropDown.id === "app-state-filter") {
        blankValue = "USA";
      }

      dropDown.value = blankValue;
    });

    const form = clearButton.closest("form");

    if (form) {
      form.submit();
    }

    return cancelEvent(e);
  });
}

export function searchForm() {
  const clearButton = <HTMLButtonElement>(
    document.querySelector("#app-clear-button")
  );
  const formElements: NodeListOf<HTMLInputElement | HTMLSelectElement> =
    document.querySelectorAll("#app-search-form input,#app-search-form select");

  const selects = <HTMLSelectElement[]>(
    Array.from(formElements).filter((el) => "SELECT" === el.tagName)
  );

  if (null !== clearButton) {
    bindClearButtonAction(clearButton, selects);
  }

  formElements.forEach((el) =>
    el.addEventListener("change", () => {
      if (el.getAttribute("data-js-enabled")) {
        return;
      }

      const form = el.closest("form");

      if (form) {
        form.submit();
      }
    }),
  );
}
