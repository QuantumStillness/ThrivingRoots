import { supabase } from '@/lib/supabase'
import Link from 'next/link'

export default async function MISSDatabase() {
  const { data: ingredients, error } = await supabase.rpc('get_all_ingredients_summary')

  if (error) {
    return <div>Error loading ingredients</div>
  }

  // Group ingredients by category
  const groupedIngredients = ingredients.reduce((acc, ingredient) => {
    if (!acc[ingredient.category]) {
      acc[ingredient.category] = []
    }
    acc[ingredient.category].push(ingredient)
    return acc
  }, {})

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-4xl font-bold mb-8">Mindful Ingredient Safety Standard (MISS) Database</h1>
      <p className="text-xl mb-8">
        The same hazards you avoid at work are in your food. Explore our database to learn about bioaccumulation, 
        harmful additives, and food safety risks.
      </p>

      {Object.entries(groupedIngredients).map(([category, items]) => (
        <section key={category} className="mb-8">
          <h2 className="text-2xl font-bold mb-4">{category}</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {items.map((ingredient) => (
              <Link 
                key={ingredient.slug}
                href={`/miss/${ingredient.slug}`}
                className="border-2 border-gray-300 p-4 rounded hover:border-black transition"
              >
                <div className="flex items-center justify-between mb-2">
                  <h3 className="text-xl font-semibold">{ingredient.name}</h3>
                  <span className={`px-2 py-1 text-sm font-bold rounded ${
                    ingredient.signal_word === 'DANGER' ? 'bg-red-500 text-white' :
                    ingredient.signal_word === 'WARNING' ? 'bg-orange-500 text-white' :
                    ingredient.signal_word === 'CAUTION' ? 'bg-yellow-500 text-black' :
                    'bg-blue-500 text-white'
                  }`}>
                    {ingredient.signal_word}
                  </span>
                </div>
                <p className="text-gray-600">{category}</p>
              </Link>
            ))}
          </div>
        </section>
      ))}
    </div>
  )
}
