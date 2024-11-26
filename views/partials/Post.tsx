import type { FC } from 'hono/jsx'
import type { postsWithBooks } from '../../data/posts'
import { Copy } from './Copy'

export type PostProps = {
  post: (typeof postsWithBooks)[number]
  isList?: boolean
}

export const Post: FC<PostProps> = ({ post, isList }) => {
  const postPath = `/post/${post.path}`
  const imageSrc = `/images/${post.image}`

  const imageBody = (
    <span>
      <img src={imageSrc} alt={post.title} />
    </span>
  )
  const image = isList ? (
    <a href={postPath} class="post__image container">
      {imageBody}
    </a>
  ) : (
    <div class="post__image container">{imageBody}</div>
  )

  const hasBook = typeof post.book === 'object' && post.book !== null
  const hasArticle = post.article_link && post.article_title

  const copy = (
    <div class="post__copy copy container">
      <blockquote>
        <Copy body={post.content} />

        {hasBook && (
          <cite>
            {post.book?.author} — {post.book?.title}
          </cite>
        )}

        {hasArticle && (
          <cite>
            <a href={post.article_link}>
              {post.article_author} — {post.article_title}
            </a>
          </cite>
        )}
      </blockquote>
    </div>
  )

  return (
    <article class="post">
      {image}
      {copy}
    </article>
  )
}
