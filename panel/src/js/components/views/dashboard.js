import { $ } from "../../utils/selectors";
import { app } from "../../app";
import { Notification } from "../notification";
import { Request } from "../../utils/request";
import { StatisticsChart } from "../statistics-chart";
import { triggerDownload } from "../../utils/forms";

export class Dashboard {
    constructor() {
        const clearCacheCommand = $("[data-command=clear-cache]");
        const makeBackupCommand = $("[data-command=make-backup]");
        const chart = $(".dashboard-chart");

        if (clearCacheCommand) {
            clearCacheCommand.addEventListener("click", () => {
                new Request(
                    {
                        method: "POST",
                        url: `${app.config.baseUri}cache/clear/`,
                        data: { "csrf-token": $("meta[name=csrf-token]").content },
                    },
                    (response) => {
                        const notification = new Notification(response.message, response.status, { icon: "check-circle" });
                        notification.show();
                    },
                );
            });
        }

        if (makeBackupCommand) {
            makeBackupCommand.addEventListener("click", function () {
                const button = this;
                button.disabled = true;
                new Request(
                    {
                        method: "POST",
                        url: `${app.config.baseUri}backup/make/`,
                        data: { "csrf-token": $("meta[name=csrf-token]").content },
                    },
                    (response) => {
                        const notification = new Notification(response.message, response.status, { icon: "check-circle" });
                        notification.show();
                        setTimeout(() => {
                            if (response.status === "success") {
                                triggerDownload(response.data.uri, $("meta[name=csrf-token]").content);
                            }
                            button.disabled = false;
                        }, 1000);
                    },
                );
            });
        }

        if (chart) {
            new StatisticsChart(chart, JSON.parse(chart.dataset.chartData));
        }
    }
}
