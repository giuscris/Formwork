import { makeSlug, validateSlug } from "../../utils/validation";
import { $ } from "../../utils/selectors";

export class SlugInput {
    constructor(element: HTMLInputElement) {
        const source = $(`[id="${element.dataset.source}"]`) as HTMLInputElement | null;
        const autoUpdate = "autoUpdate" in element.dataset && element.dataset.autoUpdate === "true";

        if (source) {
            if (autoUpdate) {
                source.addEventListener("input", () => (element.value = makeSlug(source.value)));
            } else {
                const generateButton = $(`[data-generate-slug="${element.id}"]`) as HTMLButtonElement | null;
                if (generateButton) {
                    generateButton.addEventListener("click", () => (element.value = makeSlug(source.value)));
                }
            }
        }

        const handleSlugChange = (event: Event) => {
            const target = event.target as HTMLInputElement;
            target.value = validateSlug(target.value);
        };

        element.addEventListener("keyup", handleSlugChange);
        element.addEventListener("blur", handleSlugChange);
    }
}
