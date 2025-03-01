import { cancelEvent, formToUrl } from "./utils";

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
      const form = el.closest("form");

      if (el.getAttribute("data-js-enabled")) {
        if (!form) {
          return;
        }

        if (el.getAttribute("data-no-history")) {
          el.removeAttribute("data-no-history");
          return;
        }

        const queryStr = formToUrl(form).replace(/^\.\//g, "");
        window.history.pushState(
          { graph_type: el.value },
          "",
          `${window.location.pathname}${queryStr}`,
        );
        return;
      }

      if (form) {
        form.submit();
      }
    }),
  );

  window.addEventListener("popstate", (event) => {
    let state = event.state;

    if (null === state) {
      state = { graph_type: "amount" };
    }

    if (state.hasOwnProperty("graph_type")) {
      const radios = <NodeListOf<HTMLInputElement>>(
        document.querySelectorAll('input[name="graph_type"]')
      );

      radios.forEach((radio) => {
        if (radio.value === state.graph_type) {
          radio.checked = true;
          radio.setAttribute("data-no-history", "1");
          radio.dispatchEvent(
            new Event("change", {
              bubbles: true,
            }),
          );
        }
      });
    }
  });
}
