import * as d3 from "d3";
import { formToUrl } from "./utils";

interface GraphDataPoint {
  date: Date;
  value: number;
}

interface GraphData {
  title: string;
  isDollarAmount: boolean;
  dataPoints: GraphDataPoint[];
}

interface SerializedDataPoint {
  date: string;
  value: number;
}

interface SerializedGraphData {
  title: string;
  isDollarAmount: boolean;
  dataPoints: SerializedDataPoint[];
}

function getUrl(): string {
  const form = <HTMLFormElement>document.getElementById("app-search-form");
  return formToUrl(form, { action: "graphData" });
}

async function fetchGraphData(): Promise<GraphData> {
  const response = await fetch(getUrl());

  const rawData: SerializedGraphData = await response.json();

  const dataPoints: GraphDataPoint[] = rawData.dataPoints.map((dataPoint) => ({
    date: new Date(dataPoint.date),
    value: dataPoint.value,
  }));

  return {
    title: rawData.title,
    isDollarAmount: rawData.isDollarAmount,
    dataPoints: dataPoints,
  };
}

function getContainer(): HTMLDivElement {
  return <HTMLDivElement>document.getElementById("app-graph");
}

function getRadioButtons(): NodeListOf<HTMLInputElement> {
  return <NodeListOf<HTMLInputElement>>(
    document.querySelectorAll("#app-search-form input[name=graph_type]")
  );
}

async function drawGraph(): Promise<void> {
  const container = getContainer();
  container.innerHTML =
    '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';

  const graphData = await fetchGraphData();
  draw(container, graphData);
}

function draw(container: HTMLElement, graphData: GraphData): void {
  const margin = { top: 20, right: 70, bottom: 60, left: 70 };
  const width = container.clientWidth - margin.left - margin.right;
  const height = 400 - margin.top - margin.bottom;

  const svg = d3
    .create("svg")
    .attr("width", "100%")
    .attr("height", "100%")
    .attr(
      "viewBox",
      `0 0 ${width + margin.left + margin.right} ${height + margin.top + margin.bottom}`,
    )
    .attr("preserveAspectRatio", "xMinYMin meet")
    .style("overflow", "visible");

  const g = svg
    .append("g")
    .attr("transform", `translate(${margin.left},${margin.top})`);

  const xExtent = d3.extent(
    graphData.dataPoints,
    (d: GraphDataPoint) => d.date,
  ) as [Date, Date];

  const x = d3
    .scaleTime()
    .domain(xExtent.length > 1 ? xExtent : [new Date(), new Date()])
    .range([0, width]);

  const yMax = d3.max(graphData.dataPoints, (d: GraphDataPoint) => d.value);

  const y = d3
    .scaleLinear()
    .domain([0, undefined === yMax ? 0 : yMax])
    .range([height, 0]);

  const xAxis = d3
    .axisBottom<Date>(x)
    .tickValues(d3.timeMonths(...xExtent))
    .tickFormat(d3.timeFormat("%B %Y"));

  const nf = new Intl.NumberFormat("en-US", { maximumFractionDigits: 0 });

  const yAxis = d3
    .axisLeft<number>(y)
    .tickFormat(
      (value) => (graphData.isDollarAmount ? "$" : "") + nf.format(value),
    );

  g.append("g")
    .attr("transform", `translate(0,${height})`)
    .call(xAxis)
    .selectAll("text")
    .style("text-anchor", "end")
    .attr("dx", "-0.8em")
    .attr("dy", "0.15em")
    .attr("transform", "rotate(-65)");

  g.append("g").call(yAxis);

  const line = d3
    .line<GraphDataPoint>()
    .x((d) => x(d.date))
    .y((d) => y(d.value));

  g.append("path")
    .datum(graphData.dataPoints)
    .attr("fill", "none")
    .attr("stroke", "steelblue")
    .attr("stroke-width", 2)
    .attr("d", line);

  g.append("text")
    .attr("x", width / 2)
    .attr("y", -margin.top / 2)
    .attr("text-anchor", "middle")
    .style("font-size", "16px")
    .style("font-weight", "bold")
    .text(graphData.title);

  const node = svg.node();

  if (null === node) {
    throw new Error("Could not create SVG node");
  }

  container.innerHTML = "";
  container.append(node);
}

function refreshGraph(): void {
  const radioButtons = getRadioButtons();

  radioButtons.forEach((radioButton) =>
    radioButton.setAttribute("data-locked", "1"),
  );

  const enableRadioButtons = () =>
    radioButtons.forEach((radioButton) =>
      radioButton.removeAttribute("data-locked"),
    );

  drawGraph().then(enableRadioButtons, () => {
    enableRadioButtons();
    getContainer().innerHTML =
      '<span class="text-danger">There was an error</span>';
  });
}

window.addEventListener("DOMContentLoaded", () => {
  refreshGraph();

  getRadioButtons().forEach((radioButton) =>
    radioButton.addEventListener("change", (e) => {
      if (!radioButton.getAttribute("data-locked")) {
        refreshGraph();
        return true;
      }

      e.stopPropagation();
      e.preventDefault();
      return false;
    }),
  );
});
