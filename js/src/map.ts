import { formToUrl } from "./utils";
import * as d3 from "d3";
import { GeoPath, GeoProjection } from "d3";
import geoJsonMetaByState from "./geojson-meta.json";

interface Coordinates {
  lat: number;
  lon: number;
}

function getMidPoint(state: string): Coordinates {
  const geoJsonMeta = geoJsonMetaByState.find((meta) => meta.state === state);

  if (undefined === geoJsonMeta) {
    throw new Error("Invalid state: " + state);
  }

  return geoJsonMeta.midpoint;
}

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

function getGeoProjection(state: string): GeoProjection {
  if ("USA" === state) {
    return d3.geoAlbersUsa().scale(1000);
  }

  const midPoint = getMidPoint(state);

  return d3.geoMercator().center([midPoint.lon, midPoint.lat]).scale(5000);
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

  const projection = getGeoProjection(state);

  projection.translate([width / 2, height / 2]);

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
