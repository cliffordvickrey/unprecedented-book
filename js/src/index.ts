import { Tooltip } from "bootstrap";
import "@fortawesome/fontawesome-free/css/all.css";
import "./index.scss";
import { searchForm } from "./search-form";

window.addEventListener("DOMContentLoaded", function () {
  searchForm();

  // Bootstrap widgets
  [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map(
    (tooltipTriggerEl) => new Tooltip(tooltipTriggerEl),
  );
});
