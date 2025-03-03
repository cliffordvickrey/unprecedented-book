import { cancelEvent, formToUrl } from "./utils";

enum GraphType {
  graph = "graph",
  map = "map",
}

const graphExportActions: Map<GraphType, string> = new Map();
graphExportActions.set(GraphType.graph, "graphExport");
graphExportActions.set(GraphType.map, "mapExport");

window.addEventListener("DOMContentLoaded", () => {
  const exportLink = <HTMLAnchorElement>document.getElementById("app-export");
  const form = <HTMLFormElement>document.getElementById("app-search-form");
  const selectedActionEl = <HTMLInputElement>(
    document.querySelector('input[name="action"]:checked')
  );

  if (null === exportLink || null === form || null === selectedActionEl) {
    return;
  }

  const value = selectedActionEl.value;

  if (!Object.values(GraphType).includes(value as GraphType)) {
    return;
  }

  const graphType = value as GraphType;
  const exportAction = graphExportActions.get(graphType);

  exportLink.addEventListener("click", (e) => {
    window.location.href = formToUrl(form, { action: exportAction });
    return cancelEvent(e);
  });
});
