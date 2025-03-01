import * as d3 from "d3";
import { cancelEvent, formToUrl, queryString } from "./utils";
import { rewind } from "@turf/turf";
import geoJsonMetaByState from "../../web-data/geojson-meta/geojson-meta.json";

interface Coordinates {
  lat: number;
  lon: number;
}

interface GeoJsonMeta {
  diameter: number;
  midpoint: Coordinates;
  state: string;
  stateName: string;
}

enum GraphColor {
  blue = "blue",
  red = "red",
}

interface MapDataPoint {
  jurisdiction: string;
  value: number;
}

interface MapData {
  title: string;
  color: GraphColor;
  isDollarAmount: boolean;
  isPercent: boolean;
  dataPoints: MapDataPoint[];
}

const geoJsonMetaMap = new Map<string, GeoJsonMeta>(
  geoJsonMetaByState.map((geoJsonMeta) => [geoJsonMeta.state, geoJsonMeta]),
);

const stateNameMap = new Map<string, string>(
  geoJsonMetaByState.map((geoJsonMeta) => [
    geoJsonMeta.stateName,
    geoJsonMeta.state,
  ]),
);

function scaleDiameter(diameter: number): number {
  const hardCodedScales: Map<string, number> = new Map([
    ["AK", 1000],
    ["DC", 120000],
    ["ME", 6000],
    ["RI", 30000],
    ["TX", 3500],
  ]);

  const state = geoJsonMetaByState.find(
    (meta) => meta.diameter === diameter,
  )?.state;

  if (undefined !== state && hardCodedScales.has(state)) {
    return hardCodedScales.get(state) as number;
  }

  const diametersInLogScale = geoJsonMetaByState
    .filter((meta) => !hardCodedScales.has(meta.state))
    .map((meta) => meta.diameter);

  const minDiameter = d3.min(diametersInLogScale);
  const maxDiameter = d3.max(diametersInLogScale);

  const logScale = d3
    .scaleLog<number, number>()
    .domain([
      minDiameter === undefined ? 0 : minDiameter,
      maxDiameter === undefined ? 0 : maxDiameter,
    ])
    .range([20000, 3700]);

  return logScale(diameter);
}

function getGeoJsonMeta(state: string): GeoJsonMeta {
  if (!geoJsonMetaMap.has(state)) {
    throw new Error("Map information is not available for this state");
  }

  return geoJsonMetaMap.get(state) as GeoJsonMeta;
}

function getMapDataUrl(): string {
  const form = <HTMLFormElement>document.getElementById("app-search-form");
  return formToUrl(form, { action: "mapData" });
}

function getGeoJsonUrl(): string {
  return "./?" + queryString({ action: "geoJson", state: getState() });
}

function getState(): string {
  const select = <HTMLSelectElement>document.getElementById("app-state-filter");
  return select.value;
}

function getContainer(): HTMLDivElement {
  return <HTMLDivElement>document.getElementById("app-map");
}

async function getMapData(): Promise<MapData> {
  const response = await fetch(getMapDataUrl());
  return await response.json();
}

async function getGeoData(): Promise<any> {
  const response = await fetch(getGeoJsonUrl());
  return await response.json();
}

async function plot(): Promise<void> {
  const container = getContainer();
  const state = getState();

  try {
    if (state !== "USA") {
      getGeoJsonMeta(state);
    }
  } catch (err: unknown) {
    if (err instanceof Error) {
      container.innerText = err.message;
    } else {
      console.error(err);
    }

    return;
  }

  container.innerHTML =
    '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';
  const results = await Promise.all([getGeoData(), getMapData()]);
  drawMap(container, getState(), results[0], results[1]);
}

function getGeoProjection(state: string): d3.GeoProjection {
  if ("USA" === state) {
    return d3.geoAlbersUsa().scale(1200);
  }

  const meta = getGeoJsonMeta(state);

  const scale = scaleDiameter(meta.diameter);

  console.log("Scale = " + String(scale));

  return d3
    .geoMercator()
    .center([meta.midpoint.lon, meta.midpoint.lat])
    .scale(scale);
}

function sequentialColors(color: GraphColor): d3.ScaleSequential<string> {
  switch (color) {
    case GraphColor.blue:
      return d3.scaleSequential<string>(d3.interpolateBlues);
    case GraphColor.red:
      return d3.scaleSequential<string>(d3.interpolateReds);
  }
}

function drawMap(
  container: HTMLElement,
  state: string,
  geoData: any,
  mapData: MapData,
) {
  const valueMap = new Map(
    mapData.dataPoints.map((d) => [d.jurisdiction, d.value]),
  );

  const maxValue = d3.max(mapData.dataPoints, (d) => d.value);

  let colorScale: d3.ScaleSequential<string> | d3.ScaleLinear<number, string>;

  if (mapData.isPercent) {
    let color1: string;
    let color2: string;

    switch (mapData.color) {
      case GraphColor.blue:
        color1 = "red";
        color2 = "blue";
        break;
      default:
        color1 = "blue";
        color2 = "red";
        break;
    }

    colorScale = d3
      .scaleSequential(d3.interpolateRgb(color1, color2))
      .domain([0, 100]);
  } else {
    colorScale = sequentialColors(mapData.color).domain([
      0,
      maxValue === undefined ? 0 : maxValue,
    ]);
  }

  const margin = { top: 20, right: 70, bottom: 20, left: 70 };
  const width = container.clientWidth - margin.left - margin.right;
  const height = 800 - margin.top - margin.bottom;

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

  let features: any = geoData.features;

  if ("USA" !== state) {
    features = geoData.features.map((feature: any) =>
      rewind(feature, { reverse: true }),
    );
  }

  svg
    .selectAll("path")
    .data(features)
    .enter()
    .append("path")
    .attr("d", <d3.GeoPath<any, any>>path)
    .attr("fill", (d: any) => {
      let key: string;

      if (d.properties.hasOwnProperty("name")) {
        // state name
        key = stateNameMap.get(d.properties.name.toUpperCase()) as string;
      } else if (d.properties.hasOwnProperty("ZCTA5CE20")) {
        // 2020 Census ZCTA
        key = d.properties.ZCTA5CE20;
      } else {
        return "none";
      }

      if (!valueMap.has(key)) {
        return "none";
      }

      const value = valueMap.get(key) as number;

      return colorScale(value);
    })
    .attr("stroke", "black")
    .attr("stroke-width", 0.5);

  // region legend/axis
  const legendHeight = 20;
  const legendWidth = 300;

  const legendSvg = svg
    .append("g")
    .attr("class", "legend")
    .attr(
      "transform",
      `translate(${margin.left}, ${
        height + margin.top + margin.bottom - legendHeight
      })`,
    );

  const legendGradient = legendSvg
    .append("defs")
    .append("linearGradient")
    .attr("id", "legendGradient");

  legendGradient
    .append("stop")
    .attr("offset", "0%")
    .attr("stop-color", colorScale(0));

  legendGradient
    .append("stop")
    .attr("offset", "100%")
    .attr("stop-color", colorScale(maxValue === undefined ? 0 : maxValue));

  legendSvg
    .append("rect")
    .attr("width", legendWidth)
    .attr("height", legendHeight)
    .style("fill", "url(#legendGradient)");

  const legendScale = d3
    .scaleLinear()
    .domain([0, maxValue as number])
    .range([0, legendWidth]);

  const legendAxis = d3
    .axisBottom(legendScale)
    .ticks(5)
    .tickFormat(d3.format(".2s"));

  legendSvg
    .append("g")
    .attr("transform", `translate(0, ${legendHeight})`)
    .call(legendAxis);

  svg
    .append("text")
    .attr("class", "map-title")
    .attr("x", width / 2 + margin.left)
    .attr("y", height + margin.top + margin.bottom - 5)
    .attr("text-anchor", "middle")
    .style("font-size", "16px")
    .style("font-weight", "bold")
    .text(mapData.title);
  // endregion

  const node = svg.node();

  if (null === node) {
    throw new Error("Could not create SVG node");
  }

  container.innerHTML = "";
  container.append(node);
}

function getRadioButtons(): NodeListOf<HTMLInputElement> {
  return <NodeListOf<HTMLInputElement>>(
    document.querySelectorAll("#app-search-form input[name=graph_type]")
  );
}

function refreshMap(): void {
  const radioButtons = getRadioButtons();

  radioButtons.forEach((radioButton) =>
    radioButton.setAttribute("data-locked", "1"),
  );

  const enableRadioButtons = () =>
    radioButtons.forEach((radioButton) =>
      radioButton.removeAttribute("data-locked"),
    );

  plot().then(enableRadioButtons, () => {
    enableRadioButtons();
    getContainer().innerHTML =
      '<span class="text-danger">There was an error</span>';
  });
}

window.addEventListener("DOMContentLoaded", () => {
  refreshMap();

  getRadioButtons().forEach((radioButton) =>
    radioButton.addEventListener("change", (e) => {
      if (!radioButton.getAttribute("data-locked")) {
        refreshMap();
        return true;
      }

      return cancelEvent(e);
    }),
  );
});
