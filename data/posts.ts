import books from './books.json'
import posts from './posts.json'

export const postsWithBooks = posts
  .toSorted((postA, postB) => postB.date.localeCompare(postA.date))
  .map((post) => {
    const bookId = post.book as keyof typeof books
    const book = post.book ? books[bookId] : undefined

    return book ? { ...post, book } : post
  })
