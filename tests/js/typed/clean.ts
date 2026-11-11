export async function loadGreeting(): Promise<string> {
    return await Promise.resolve('Hello')
}

export async function greet(): Promise<string> {
    return await loadGreeting()
}
