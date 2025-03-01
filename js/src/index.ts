import { Tooltip } from "bootstrap";
import "@fortawesome/fontawesome-free/css/all.css";
import "./index.scss";
import { searchForm } from "./search-form";
import { cancelEvent } from "./utils";

window.addEventListener("DOMContentLoaded", function () {
  searchForm();

  // Bootstrap widgets
  const tooltips = [...document.querySelectorAll('[data-bs-toggle="tooltip"]')];

  tooltips.map((tooltip) => {
    tooltip.addEventListener("click", (e) => cancelEvent(e));
    return new Tooltip(tooltip);
  });
});
