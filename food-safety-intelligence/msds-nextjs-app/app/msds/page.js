import { supabase } from '@/lib/supabase'

export default async function MSDSPage() {
  const { data: ingredients, error } = await supabase.rpc('get_all_ingredients_summary')

  if (error) {
    return <p>Error loading data. Please try again later.</p>
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-4xl font-bold mb-8">Mindful Substance Data Series (MSDS)</h1>
      
      <div className="overflow-x-auto">
        <table className="min-w-full bg-white border border-gray-200">
          <thead>
            <tr className="bg-gray-100">
              <th className="py-2 px-4 border-b">Ingredient</th>
              <th className="py-2 px-4 border-b">Category</th>
              <th className="py-2 px-4 border-b">Series</th>
              <th className="py-2 px-4 border-b">Total Score</th>
              <th className="py-2 px-4 border-b">Signal Word</th>
            </tr>
          </thead>
          <tbody>
            {ingredients.map((ingredient) => (
              <tr key={ingredient.slug} className="hover:bg-gray-50">
                <td className="py-2 px-4 border-b">
                  <a href={`/msds/${ingredient.slug}`} className="text-blue-600 hover:underline">
                    {ingredient.name}
                  </a>
                </td>
                <td className="py-2 px-4 border-b">{ingredient.category}</td>
                <td className="py-2 px-4 border-b">{ingredient.series}</td>
                <td className="py-2 px-4 border-b">{ingredient.total_score}</td>
                <td className="py-2 px-4 border-b">{ingredient.signal_word}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
