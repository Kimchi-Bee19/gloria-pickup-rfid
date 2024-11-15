/**
 * @see https://stackoverflow.com/a/70727137
 */
export const sub2regex = (topic: string) => {
    return new RegExp(
        `^${topic}\$`.replaceAll("+", "[^/]*").replace("/#", "(|/.*)")
    );
};
