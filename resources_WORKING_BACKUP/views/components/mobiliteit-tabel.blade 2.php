{{-- resources/views/components/mobiliteit-tabel.blade.php --}}
<div class="mt-8 mb-8">
    <h2 class="text-xl font-bold mb-4">Functionele controle/ Mobiliteit</h2>
    <table class="min-w-full bg-white rounded-lg overflow-hidden shadow">
        <thead class="bg-blue-100">
            <tr>
                <th class="py-2 px-4 text-left">Test</th>
                <th class="py-2 px-4 text-center">Links</th>
                <th class="py-2 px-4 text-center">Rechts</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mobiliteit as $row)
                <tr class="border-b">
                    <td class="py-3 px-4 font-semibold align-top">{{ $row['test'] }}</td>
                    <td class="py-3 px-4 align-top">
                        <div class="flex items-center justify-center mb-1">
                            <div class="flex w-32 h-6 rounded overflow-hidden">
                                <div class="w-1/4 h-full bg-green-500"></div>
                                <div class="w-1/4 h-full bg-green-300"></div>
                                <div class="w-1/4 h-full bg-yellow-400"></div>
                                <div class="w-1/4 h-full bg-red-500"></div>
                            </div>
                        </div>
                        <div class="text-yellow-600 font-bold text-center mb-1">{{ $row['links']['score'] }}</div>
                        <div class="text-gray-700 text-sm">{{ $row['links']['desc'] }}</div>
                    </td>
                    <td class="py-3 px-4 align-top">
                        <div class="flex items-center justify-center mb-1">
                            <div class="flex w-32 h-6 rounded overflow-hidden">
                                <div class="w-1/4 h-full bg-green-500"></div>
                                <div class="w-1/4 h-full bg-green-300"></div>
                                <div class="w-1/4 h-full bg-yellow-400"></div>
                                <div class="w-1/4 h-full bg-red-500"></div>
                            </div>
                        </div>
                        <div class="text-yellow-600 font-bold text-center mb-1">{{ $row['rechts']['score'] }}</div>
                        <div class="text-gray-700 text-sm">{{ $row['rechts']['desc'] }}</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
