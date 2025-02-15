import { formToUrl } from "./utils";
import * as d3 from "d3";
import { GeoPath, GeoProjection } from "d3";

function getUrl(): string {
  const form = <HTMLFormElement>document.getElementById("app-search-form");
  return formToUrl(form, { action: "geoJson" });
}

function getState(): string {
  const select = <HTMLSelectElement>document.getElementById("app-state-filter");
  return select.value;
}

function getContainer(): HTMLDivElement {
  return <HTMLDivElement>document.getElementById("app-map");
}

async function getGeoData(): Promise<any> {
  const response = await fetch(getUrl());
  return await response.json();
}

async function refreshMap(): Promise<void> {
  const geoData = await getGeoData();
  drawMap(getContainer(), getState(), geoData);
}

function drawMap(container: HTMLElement, state: string, geoData: any) {
  const margin = { top: 20, right: 70, bottom: 20, left: 70 };
  const width = container.clientWidth - margin.left - margin.right;
  const height = 550 - margin.top - margin.bottom;

  const svg = d3
    .create("svg")
    .attr("width", "100%")
    .attr("height", "100%")
    .attr(
      "viewBox",
      `0 0 ${width + margin.left + margin.right} ${height + margin.top + margin.bottom}`,
    )
    .style("overflow", "visible");

  let projection: GeoProjection;

  // @todo heights/scale/coordinates for each state
  if ("ME" === state) {
    projection = d3
      .geoMercator()
      .translate([width / 2, height / 2])
      .center([-69.445469, 45.253783])
      .scale(5000);
  } else {
    projection = d3
      .geoAlbersUsa()
      .translate([width / 2, height / 2])
      .scale(1000);
  }

  const path = d3.geoPath().projection(projection);

  svg
    .selectAll("path")
    .data(geoData.features)
    .enter()
    .append("path")
    .attr("d", <GeoPath<any, any>>path)
    .attr("fill", "none")
    .attr("stroke", "black")
    .attr("stroke-width", 0.5);

  const node = svg.node();

  if (null === node) {
    throw new Error("Could not create SVG node");
  }

  container.innerHTML = "";
  container.append(node);
}

window.addEventListener("DOMContentLoaded", () => {
  refreshMap().then(
    () => console.log("Map drawn successfully!"),
    (err) => {
      throw err;
    },
  );
});
