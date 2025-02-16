import { formToUrl } from "./utils";
import * as d3 from "d3";
import geoJsonMetaByState from "../../web-data/geojson-meta/geojson-meta.json";
import { rewind } from "@turf/rewind";

interface Coordinates {
  lat: number;
  lon: number;
}

interface GeoJsonMeta {
  diameter: number;
  midpoint: Coordinates;
  state: string;
}

interface MapDataPoint {
  jurisdiction: string;
  value: number;
}

interface MapData {
  title: string;
  isDollarAmount: boolean;
  dataPoints: MapDataPoint[];
}

function scaleDiameter(diameter: number): number {
  const hardCodedScales: Map<string, number> = new Map([
    ["AK", 1000],
    ["DC", 120000],
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
  const geoJsonMeta = geoJsonMetaByState.find((meta) => meta.state === state);

  if (undefined === geoJsonMeta) {
    throw new Error("Invalid state: " + state);
  }

  return geoJsonMeta;
}

function getMapDataUrl(): string {
  const form = <HTMLFormElement>document.getElementById("app-search-form");
  return formToUrl(form, { action: "mapData" });
}

function getGeoJsonUrl(): string {
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

async function getMapData(): Promise<MapData> {
  const response = await fetch(getMapDataUrl());
  return await response.json();
}

async function getGeoData(): Promise<any> {
  const response = await fetch(getGeoJsonUrl());
  return await response.json();
}

async function refreshMap(): Promise<void> {
  const results = await Promise.all([getGeoData(), getMapData()]);
  drawMap(getContainer(), getState(), results[0], results[1]);
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

  const colorScale = d3
    .scaleSequential(d3.interpolateBlues)
    .domain([0, maxValue === undefined ? 0 : maxValue]);

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
        key = d.properties.name.toUpperCase();
      } else if (d.properties.hasOwnProperty("ZCTA5CE20")) {
        // 2020 Census ZCTA
        key = d.properties.ZCTA5CE20;
      } else {
        return "none";
      }

      if (!valueMap.has(key)) {
        return "none";
      }

      const value = valueMap.get(key);

      if (undefined === value) {
        return "none";
      }

      return colorScale(value);
    })
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
